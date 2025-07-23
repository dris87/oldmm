<?php
// Session kezelés ellenőrzéssel
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params('86400');
    ini_set("session.gc_maxlifetime", 86400);
    ini_set("session.cookie_lifetime", 86400);
    session_start();
}

require_once 'config.php';
require_once 'auth_helper.php';

// Autentikáció ellenőrzése
if (!isset($_SESSION['query_user'])) {
    if (isset($_COOKIE['remember_token'])) {
        $auth = validateRememberToken($con, $_COOKIE['remember_token']);
        if ($auth) {
            setcookie(
                'remember_token',
                $auth['token'],
                time() + (24 * 60 * 60),
                '/',
                '',
                true,
                true
            );
        } else {
            setcookie(
                'remember_token',
                '',
                time() - 3600,
                '/',
                '',
                true,
                true
            );
            header('Location: login.php');
            exit;
        }
    } else {
        header('Location: login.php');
        exit;
    }
}

require_once 'PHPExcel/Classes/PHPExcel.php';
require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';

ini_set('memory_limit', '-1');
ini_set('date.timezone', 'Europe/Budapest');
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

// ZIP letöltés kezelése
if (isset($_GET['download_zip'])) {
    $zipFile = 'company_reports/osszes_ceg_jelentkezesek.zip';
    
    if (file_exists($zipFile)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="osszes_ceg_jelentkezesek.zip"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);
        exit;
    } else {
        echo "ZIP fájl nem található!";
        exit;
    }
}

// Újragenerálás kezelése
if (isset($_GET['regenerate'])) {
    // Töröljük az összes létező fájlt
    if (is_dir('company_reports')) {
        $files = glob('company_reports/*.xlsx');
        foreach ($files as $file) {
            unlink($file);
        }
        $zipFile = 'company_reports/osszes_ceg_jelentkezesek.zip';
        if (file_exists($zipFile)) {
            unlink($zipFile);
        }
    }
    // Átirányítjuk ugyanerre az oldalra újrageneráláshoz
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Excel fájl beolvasása és cég ID-k kinyerése
function getCompanyIdsFromExcel($filename) {
    try {
        $objPHPExcel = PHPExcel_IOFactory::load($filename);
        $worksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        
        $companyIds = array();
        
        // ELSŐ sortól kezdjük (nincs header)
        for ($row = 1; $row <= $highestRow; $row++) {
            $colB = $worksheet->getCell('B' . $row)->getValue();
            $colD = $worksheet->getCell('D' . $row)->getValue();
            $colE = $worksheet->getCell('E' . $row)->getValue();
            
            $companyName = $colB;
            $idD = $colD;
            $idE = $colE;
            
            if (!empty($companyName)) {
                if (!isset($companyIds[$companyName])) {
                    $companyIds[$companyName] = array();
                }
                
                if (!empty($idD)) {
                    $companyIds[$companyName][] = (int)$idD;
                }
                if (!empty($idE)) {
                    $companyIds[$companyName][] = (int)$idE;
                }
                
                $companyIds[$companyName] = array_unique($companyIds[$companyName]);
            }
        }
        
        return $companyIds;
    } catch (Exception $e) {
        return false;
    }
}

// Jelentkezések lekérdezése egy cég ID-k alapján
function getApplicationsForCompany($con, $companyIds) {
    if (empty($companyIds)) {
        return array();
    }
    
    $placeholders = str_repeat('?,', count($companyIds) - 1) . '?';
    
    $sql = "SELECT 
        f.id AS ceg_id,
        f.name AS ceg_nev,
        o.title AS hirdetes_cime,
        CONCAT('https://mumi.hu/hu/allas/', o.slug) AS hirdetes_url,
        qe.name AS jelentkezo_neve,
        qe.email AS email,
        qe.telephone AS telefon,
        qe.county AS megye,
        qe.city AS varos,
        qe.cv_url AS oneletrajz_link,
        qe.positions AS erdekli_poziciok,
        qe.driving_license AS veztoi_engedely,
        qe.created_at AS jelentkezes_datuma
    FROM firm f
    INNER JOIN offer o ON f.id = o.firm_id
    INNER JOIN query_employees qe ON o.id = qe.offer_id
    WHERE f.id IN ($placeholders)
        AND o.minimal_package = 1
        AND qe.created_at >= '2025-04-28'
        AND (
            o.minimal_without_cv = 0 
            OR 
            (o.minimal_without_cv = 1 AND qe.cv_url IS NOT NULL AND qe.cv_url != '')
        )
    ORDER BY qe.created_at DESC";
    
    try {
        $stmt = $con->prepare($sql);
        $stmt->execute($companyIds);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Duplikáció szűrés - ugyanaz a jelentkező ugyanazokkal az adatokkal
        $unique = array();
        $seen = array();
        
        foreach ($results as $row) {
            // Egyedi kulcs készítése az adatok alapján (cég_id nélkül)
            $key = md5($row['jelentkezo_neve'] . '|' . $row['email'] . '|' . $row['telefon'] . '|' . 
                     $row['megye'] . '|' . $row['varos'] . '|' . $row['oneletrajz_link'] . '|' . 
                     $row['erdekli_poziciok'] . '|' . $row['veztoi_engedely']);
            
            if (!isset($seen[$key])) {
                $unique[] = $row;
                $seen[$key] = true;
            }
        }
        
        return $unique;
    } catch (PDOException $e) {
        return array();
    }
}

// Részletes cég adatok lekérdezése (hirdetésekkel és jelentkezésekkel)
function getDetailedCompanyData($con, $companyIds) {
    if (empty($companyIds)) {
        return array();
    }
    
    $placeholders = str_repeat('?,', count($companyIds) - 1) . '?';
    
    $sql = "SELECT 
        f.id AS ceg_id,
        f.name AS ceg_nev,
        o.id AS hirdetes_id,
        o.title AS hirdetes_cime,
        o.created_at AS hirdetes_letrehozva,
        o.expire_date AS hirdetes_lejarat,
        CONCAT('https://mumi.hu/hu/allas/', o.slug) AS hirdetes_url,
        qe.name AS jelentkezo_neve,
        qe.email AS email,
        qe.telephone AS telefon,
        qe.county AS megye,
        qe.city AS varos,
        qe.cv_url AS oneletrajz_link,
        qe.positions AS erdekli_poziciok,
        qe.driving_license AS veztoi_engedely,
        qe.created_at AS jelentkezes_datuma
    FROM firm f
    INNER JOIN offer o ON f.id = o.firm_id
    LEFT JOIN query_employees qe ON o.id = qe.offer_id AND qe.created_at >= '2025-04-28'
        AND (
            o.minimal_without_cv = 0 
            OR 
            (o.minimal_without_cv = 1 AND qe.cv_url IS NOT NULL AND qe.cv_url != '')
        )
    WHERE f.id IN ($placeholders)
        AND o.minimal_package = 1
    ORDER BY o.created_at DESC, qe.created_at DESC";
    
    try {
        $stmt = $con->prepare($sql);
        $stmt->execute($companyIds);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Csoportosítás cégek és hirdetések szerint
        $companies = array();
        
        foreach ($results as $row) {
            $cegId = $row['ceg_id'];
            $hirdetesId = $row['hirdetes_id'];
            
            if (!isset($companies[$cegId])) {
                $companies[$cegId] = array(
                    'nev' => $row['ceg_nev'],
                    'hirdetesek' => array(),
                    'osszes_hirdetes' => 0,
                    'hirdetes_jelentkezokkel' => 0
                );
            }
            
            if (!isset($companies[$cegId]['hirdetesek'][$hirdetesId])) {
                $companies[$cegId]['hirdetesek'][$hirdetesId] = array(
                    'cim' => $row['hirdetes_cime'],
                    'letrehozva' => $row['hirdetes_letrehozva'],
                    'lejarat' => $row['hirdetes_lejarat'],
                    'url' => $row['hirdetes_url'],
                    'jelentkezok' => array()
                );
                $companies[$cegId]['osszes_hirdetes']++;
            }
            
            // Ha van jelentkező
            if (!empty($row['jelentkezo_neve'])) {
                // Duplikáció szűrés jelentkezőkre hirdetésenként
                $key = md5($row['jelentkezo_neve'] . '|' . $row['email'] . '|' . $row['telefon'] . '|' . 
                         $row['megye'] . '|' . $row['varos'] . '|' . $row['oneletrajz_link'] . '|' . 
                         $row['erdekli_poziciok'] . '|' . $row['veztoi_engedely']);
                
                $found = false;
                foreach ($companies[$cegId]['hirdetesek'][$hirdetesId]['jelentkezok'] as $existing) {
                    $existingKey = md5($existing['nev'] . '|' . $existing['email'] . '|' . $existing['telefon'] . '|' . 
                                     $existing['megye'] . '|' . $existing['varos'] . '|' . $existing['oneletrajz_link'] . '|' . 
                                     $existing['erdekli_poziciok'] . '|' . $existing['veztoi_engedely']);
                    if ($key === $existingKey) {
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $companies[$cegId]['hirdetesek'][$hirdetesId]['jelentkezok'][] = array(
                        'nev' => $row['jelentkezo_neve'],
                        'email' => $row['email'],
                        'telefon' => $row['telefon'],
                        'megye' => $row['megye'],
                        'varos' => $row['varos'],
                        'oneletrajz_link' => $row['oneletrajz_link'],
                        'erdekli_poziciok' => $row['erdekli_poziciok'],
                        'veztoi_engedely' => $row['veztoi_engedely'],
                        'jelentkezes_datuma' => $row['jelentkezes_datuma']
                    );
                }
            }
        }
        
        // Számoljuk a jelentkezőkkel rendelkező hirdetéseket
        foreach ($companies as $cegId => &$company) {
            foreach ($company['hirdetesek'] as $hirdetesId => $hirdetes) {
                if (!empty($hirdetes['jelentkezok'])) {
                    $company['hirdetes_jelentkezokkel']++;
                }
            }
        }
        
        return $companies;
    } catch (PDOException $e) {
        return array();
    }
}

// Excel fájl generálása
function generateExcelForCompany($companyName, $applications) {
    try {
        $objPHPExcel = new PHPExcel();
        $activeSheet = $objPHPExcel->getActiveSheet();
        
        $headers = [
            'A1' => 'Cég név', 'B1' => 'Hirdetés címe', 'C1' => 'Jelentkező neve', 
            'D1' => 'Email', 'E1' => 'Telefon', 'F1' => 'Megye',
            'G1' => 'Város', 'H1' => 'Önéletrajz link', 'I1' => 'Vezetői engedély', 
            'J1' => 'Jelentkezés dátuma'
        ];
        
        foreach ($headers as $cell => $value) {
            $activeSheet->SetCellValue($cell, $value);
            // Fejléc formázása
            $activeSheet->getStyle($cell)->getFont()->setBold(true);
        }
        
        $row = 2;
        foreach ($applications as $app) {
            $activeSheet->SetCellValue('A' . $row, $app['ceg_nev']);
            $activeSheet->SetCellValue('B' . $row, $app['hirdetes_cime']);
            $activeSheet->SetCellValue('C' . $row, $app['jelentkezo_neve']);
            $activeSheet->SetCellValue('D' . $row, $app['email']);
            $activeSheet->SetCellValue('E' . $row, $app['telefon']);
            $activeSheet->SetCellValue('F' . $row, $app['megye']);
            $activeSheet->SetCellValue('G' . $row, $app['varos']);
            
            // Önéletrajz link - kattintható link
            if (!empty($app['oneletrajz_link'])) {
                $activeSheet->getCell('H' . $row)->getHyperlink()->setUrl($app['oneletrajz_link']);
                $activeSheet->SetCellValue('H' . $row, $app['oneletrajz_link']);
                $activeSheet->getStyle('H' . $row)->getFont()->setUnderline(true);
                $activeSheet->getStyle('H' . $row)->getFont()->getColor()->setRGB('0000FF');
            } else {
                $activeSheet->SetCellValue('H' . $row, '');
            }
            
            $activeSheet->SetCellValue('I' . $row, $app['veztoi_engedely']);
            $activeSheet->SetCellValue('J' . $row, $app['jelentkezes_datuma']);
            $row++;
        }
        
        // Oszlopszélességek automatikus igazítása
        foreach (range('A', 'J') as $column) {
            $activeSheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        $fileName = str_replace(' ', '_', $companyName);
        $fileName = preg_replace('/[^A-Za-z0-9_\-áéíóöőúüűÁÉÍÓÖŐÚÜŰ]/', '', $fileName);
        $fileName = $fileName . '.xlsx';
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('company_reports/' . $fileName);
        
        return $fileName;
    } catch (Exception $e) {
        return false;
    }
}

// ZIP fájl készítése
function createZipFile($files) {
    $zipFile = 'company_reports/osszes_ceg_jelentkezesek.zip';
    
    if (file_exists($zipFile)) {
        unlink($zipFile);
    }
    
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
        return false;
    }
    
    foreach ($files as $file) {
        if (!empty($file['file'])) {
            $filePath = 'company_reports/' . $file['file'];
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $file['file']);
            }
        }
    }
    
    $zip->close();
    return true;
}

// Ellenőrizzük hogy léteznek-e már a fájlok
function checkExistingFiles($companyIds) {
    $existingFiles = array();
    $totalApplications = 0;
    
    foreach ($companyIds as $companyName => $ids) {
        $fileName = str_replace(' ', '_', $companyName);
        $fileName = preg_replace('/[^A-Za-z0-9_\-áéíóöőúüűÁÉÍÓÖŐÚÜŰ]/', '', $fileName);
        $fileName = $fileName . '.xlsx';
        $filePath = 'company_reports/' . $fileName;
        
        if (file_exists($filePath)) {
            // Becsüljük a jelentkezések számát a fájl mérete alapján (nem pontos, de gyors)
            $fileSize = filesize($filePath);
            $estimatedCount = max(1, intval(($fileSize - 6000) / 200)); // Durva becslés
            
            $existingFiles[] = array(
                'company' => $companyName,
                'file' => $fileName,
                'count' => $estimatedCount
            );
            $totalApplications += $estimatedCount;
        }
    }
    
    return array($existingFiles, $totalApplications);
}

// Könyvtár létrehozása
if (!is_dir('company_reports')) {
    mkdir('company_reports', 0755, true);
}

$excelFile = 'kikuldendo_cv.xlsx';
if (!file_exists($excelFile)) {
    echo "<div class='alert alert-danger'>Hiba: '$excelFile' fájl nem található!</div>";
    exit;
}

$companyIds = getCompanyIdsFromExcel($excelFile);
if (!$companyIds) {
    echo "<div class='alert alert-danger'>Hiba: Nem sikerült beolvasni a cég ID-kat!</div>";
    exit;
}

// Ellenőrizzük a létező fájlokat
list($existingFiles, $estimatedTotal) = checkExistingFiles($companyIds);
$needsGeneration = (count($existingFiles) != count($companyIds)) || empty($existingFiles);

$generatedFiles = array();
$totalApplications = 0;
$detailedCompanies = array();
$isFromCache = false;

if (!$needsGeneration) {
    // Használjuk a cache-t
    $generatedFiles = $existingFiles;
    $totalApplications = $estimatedTotal;
    $isFromCache = true;
    
    // Betöltjük a részletes adatokat a legördülő ablakokhoz
    foreach ($companyIds as $companyName => $ids) {
        $detailedData = getDetailedCompanyData($con, $ids);
        if (!empty($detailedData)) {
            $detailedCompanies[$companyName] = $detailedData[array_keys($detailedData)[0]];
        }
        
        // Ha nincs a generált fájlokban, de van a company listában, akkor hozzáadjuk 0 jelentkezéssel
        $found = false;
        foreach ($generatedFiles as $file) {
            if ($file['company'] === $companyName) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $generatedFiles[] = array(
                'company' => $companyName,
                'file' => null,
                'count' => 0,
                'company_ids' => implode(', ', $ids)
            );
        } else {
            // Hozzáadjuk a cég ID-kat a meglévő elemekhez is
            for ($i = 0; $i < count($generatedFiles); $i++) {
                if ($generatedFiles[$i]['company'] === $companyName) {
                    $generatedFiles[$i]['company_ids'] = implode(', ', $ids);
                    break;
                }
            }
        }
    }
    
    // Ellenőrizzük a ZIP fájlt
    $zipFile = 'company_reports/osszes_ceg_jelentkezesek.zip';
    if (!file_exists($zipFile)) {
        createZipFile($generatedFiles);
    }
} else {
    // Generálnunk kell
    foreach ($companyIds as $companyName => $ids) {
        $applications = getApplicationsForCompany($con, $ids);
        $detailedData = getDetailedCompanyData($con, $ids);
        
        $count = count($applications);
        $totalApplications += $count;
        
        // Részletes adatok mentése a legördülő ablakhoz
        if (!empty($detailedData)) {
            $detailedCompanies[$companyName] = $detailedData[array_keys($detailedData)[0]];
        }
        
        $fileName = null;
        if ($count > 0) {
            $fileName = generateExcelForCompany($companyName, $applications);
        }
        
        // Minden céget hozzáadunk a listához
        $generatedFiles[] = array(
            'company' => $companyName,
            'file' => $fileName,
            'count' => $count,
            'company_ids' => implode(', ', $ids)
        );
    }
    
    // ZIP készítése (csak a fájlokkal rendelkező cégekből)
    $filesForZip = array_filter($generatedFiles, function($file) {
        return !empty($file['file']);
    });
    if (!empty($filesForZip)) {
        createZipFile($filesForZip);
    }
}

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Céges jelentkezések Excel generálása</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h1 class="h4 mb-0"><i class="fas fa-file-excel me-2"></i>Céges jelentkezések Excel generálása</h1>
                        <?php if ($isFromCache): ?>
                            <span class="badge bg-success">
                                <i class="fas fa-clock me-1"></i>Cache-ből betöltve
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h5><?= count($companyIds) ?></h5>
                                        <small>Feldolgozott cég</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5><?= count($generatedFiles) ?></h5>
                                        <small>Generált Excel fájl</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h5><?= $totalApplications ?></h5>
                                        <small>Összes jelentkezés</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($generatedFiles)): ?>
                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5>Generált fájlok:</h5>
                                <div>
                                    <a href="?regenerate=1" class="btn btn-warning me-2" 
                                       onclick="return confirm('Biztosan újragenerálja az összes fájlt? Ez eltarthat egy ideig.')">
                                        <i class="fas fa-sync-alt me-2"></i>Újragenerálás
                                    </a>
                                    <a href="?download_zip=1" class="btn btn-primary">
                                        <i class="fas fa-download me-2"></i>Összes letöltése ZIP-ben
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive mt-3">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Cég neve <small class="text-muted">(kattints a részletekért)</small></th>
                                            <th class="text-center">Jelentkezések</th>
                                            <th class="text-center">Letöltés</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($generatedFiles as $index => $file): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <?php if ($file['count'] > 0): ?>
                                                            <a href="#" data-bs-toggle="collapse" data-bs-target="#company-<?= $index ?>" 
                                                               class="text-decoration-none fw-bold">
                                                                <?= htmlspecialchars($file['company']) ?>
                                                                <i class="fas fa-chevron-down ms-2"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="fw-bold text-muted"><?= htmlspecialchars($file['company']) ?></span>
                                                        <?php endif; ?>
                                                        <br>
                                                        <small class="text-muted">ID: <?= $file['company_ids'] ?></small>
                                                    </div>
                                                    <?php if ($file['count'] == 0): ?>
                                                        <span class="badge bg-secondary">Nincs jelentkező</span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <?php if ($file['count'] > 0 && isset($detailedCompanies[$file['company']])): ?>
                                                <div class="collapse mt-3" id="company-<?= $index ?>">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <?php 
                                                            $company = $detailedCompanies[$file['company']]; 
                                                            // Csak jelentkezőkkel rendelkező hirdetések
                                                            $hirdetesekJelentkezokkel = array();
                                                            
                                                            foreach ($company['hirdetesek'] as $hirdetesId => $hirdetes) {
                                                                if (!empty($hirdetes['jelentkezok'])) {
                                                                    $hirdetesekJelentkezokkel[$hirdetesId] = $hirdetes;
                                                                }
                                                            }
                                                            
                                                            $nullaJelentkezosHirdetesek = $company['osszes_hirdetes'] - $company['hirdetes_jelentkezokkel'];
                                                            ?>
                                                            
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <h6 class="mb-0">Hirdetések jelentkezésekkel</h6>
                                                                <?php if ($nullaJelentkezosHirdetesek > 0): ?>
                                                                    <span class="badge bg-warning">
                                                                        <?= $nullaJelentkezosHirdetesek ?> hirdetésre 0 jelentkező
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                            
                                                            <?php if (!empty($hirdetesekJelentkezokkel)): ?>
                                                                <?php foreach ($hirdetesekJelentkezokkel as $hirdetesId => $hirdetes): ?>
                                                                    <div class="card mb-3 border-primary">
                                                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <h6 class="mb-0"><?= htmlspecialchars($hirdetes['cim']) ?></h6>
                                                                                <small class="text-muted">
                                                                                    ID: <?= $hirdetesId ?> | 
                                                                                    Létrehozva: <?= date('Y-m-d', strtotime($hirdetes['letrehozva'])) ?> | 
                                                                                    Lejárat: <?= $hirdetes['lejarat'] ?> | 
                                                                                    <a href="<?= $hirdetes['url'] ?>" target="_blank" class="text-decoration-none">
                                                                                        <i class="fas fa-external-link-alt"></i> Megtekintés
                                                                                    </a>
                                                                                </small>
                                                                            </div>
                                                                            <div>
                                                                                <span class="badge bg-primary fs-6">
                                                                                    <?= count($hirdetes['jelentkezok']) ?> jelentkező
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <div class="card-body">
                                                                            <div class="row">
                                                                                <?php foreach ($hirdetes['jelentkezok'] as $jelentkezo): ?>
                                                                                    <div class="col-md-6 mb-3">
                                                                                        <div class="border rounded p-3 h-100">
                                                                                            <h6 class="mb-1"><?= htmlspecialchars($jelentkezo['nev']) ?></h6>
                                                                                            <div class="small text-muted">
                                                                                                <div><i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($jelentkezo['email']) ?></div>
                                                                                                <div><i class="fas fa-phone me-1"></i> <?= htmlspecialchars($jelentkezo['telefon']) ?></div>
                                                                                                <div><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($jelentkezo['megye']) ?>, <?= htmlspecialchars($jelentkezo['varos']) ?></div>
                                                                                                <div><i class="fas fa-clock me-1"></i> <?= date('m-d H:i', strtotime($jelentkezo['jelentkezes_datuma'])) ?></div>
                                                                                                <?php if (!empty($jelentkezo['oneletrajz_link'])): ?>
                                                                                                    <div class="mt-2">
                                                                                                        <a href="<?= $jelentkezo['oneletrajz_link'] ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                                                                            <i class="fas fa-file me-1"></i> CV megtekintés
                                                                                                        </a>
                                                                                                    </div>
                                                                                                <?php endif; ?>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php endforeach; ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <div class="alert alert-info">
                                                                    <i class="fas fa-info-circle me-2"></i>
                                                                    Ennél a cégnél nincs jelentkező egyik hirdetésre sem.
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($file['count'] > 0): ?>
                                                    <span class="badge bg-secondary"><?= $file['count'] ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($file['count'] > 0 && !empty($file['file'])): ?>
                                                    <a href="company_reports/<?= $file['file'] ?>" class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Nincsenek elérhető jelentkezések a megadott feltételekkel.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>