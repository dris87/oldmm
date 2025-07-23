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
    // Nincs aktív session, próbáljuk meg a remember token-t
    if (isset($_COOKIE['remember_token'])) {
        $auth = validateRememberToken($con, $_COOKIE['remember_token']);
        if ($auth) {
            // Frissítjük a cookie-t az új tokennel
            setcookie(
                'remember_token',
                $auth['token'],
                time() + (24 * 60 * 60),
                '/',
                '',
                true,    // secure
                true     // httponly
            );
        } else {
            // Érvénytelen token, töröljük a cookie-t
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
ini_set('display_errors', 0);




if($_POST['states']) {
   if(in_array('Budapest', $_POST['states'], true)) {
      $budapestAreas=[
         "Budapest VI. kerület", "Budapest I. kerület", "Budapest II. kerület", "Budapest III. kerület", "Budapest IV. kerület", "Budapest V. kerület", "Budapest VII. kerület", "Budapest VIII. kerület", "Budapest IX. kerület", "Budapest X. kerület", "Budapest XI. kerület", "Budapest XII. kerület", "Budapest XIII. kerület", "Budapest XIV. kerület", "Budapest XV. kerület", "Budapest XVI. kerület", "Budapest XVII. kerület", "Budapest XVIII. kerület", "Budapest XIX. kerület", "Budapest XX. kerület", "Budapest XXI. kerület", "Budapest XXII. kerület", "Budapest XXIII. kerület", "Budapest"
      ];

      $_POST['states'] = array_merge($_POST['states'], $budapestAreas);

   }

}


// Input paraméterek feldolgozása
$categories = !empty($_POST['categories']) ? $_POST['categories'] : NULL;
$counties = !empty($_POST['counties']) ? $_POST['counties'] : NULL;
$cities = !empty($_POST['states']) ? $_POST['states'] : NULL;
$name = !empty($_POST['name']) ? $_POST['name'] : NULL;
$email = !empty($_POST['email']) ? $_POST['email'] : NULL;
$telephone = !empty($_POST['telephone']) ? $_POST['telephone'] : NULL;
$birthday_over = !empty($_POST['birthday_over']) ? $_POST['birthday_over'] : NULL;
$birthday_down = !empty($_POST['birthday_down']) ? $_POST['birthday_down'] : NULL;
$traveling = isset($_POST['traveling']) ? $_POST['traveling'] : NULL;
$foreigner = isset($_POST['foreigner']) ? $_POST['foreigner'] : NULL;
$drivings = !empty($_POST['driving_license']) ? $_POST['driving_license'] : NULL;
$languages = !empty($_POST['languages']) ? $_POST['languages'] : NULL;
$sources = !empty($_POST['sources']) ? $_POST['sources'] : NULL;
$status = isset($_POST['status']) ? $_POST['status'] : NULL;
$cv = isset($_POST['cv']) ? $_POST['cv'] : NULL;
$offers = !empty($_POST['offer']) ? $_POST['offer'] : NULL;

if($_GET['m'] === "q") {
    // DataTables parameters
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 50;
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    $orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'DESC';

    try {
        // Get filtered data with pagination
        $results = getCvs($con, $cities, $categories, $counties, $name, $email, $telephone, 
                         $birthday_over, $birthday_down, $traveling, $foreigner, $languages, 
                         $drivings, $sources, $cv, $status, $offers, $search, $orderColumn, 
                         $orderDir, $start, $length);
        
        // Format data for DataTables
        $data = [];
        foreach ($results['data'] as $cv) {
            $selectText = '<select onchange="statusChange(this, '.$cv['id'].')" id="change-status'.$cv['id'].'" data-id="'.$cv['id'].'" >';
            foreach($statusArray as $key => $status) {
                $selected = ($cv['status'] == $key ) ? 'selected' : '';
                $selectText .= '<option value="'.$key.'" '.$selected.'>'.$status.'</option>';
            }
            $selectText .= "</select>";
            
            $offer = getOffer($con, $cv['offer_id']);
            $firm = getFirm($con, $offer['firm_id']);
            $firm_name = (isset($firm['name'])) ? $firm['name'].' - ' : '';
            
            $data[] = [
                "id" => $cv['id'],
                "status" => $selectText,
                "name" => '<a href="edit.php?e='.$cv['id'].'" target="_blank" style="text-decoration:none;" title="Szerkesztés">'.$cv['name'].'</a>',
                "email" => $cv['email'],
                "telephone" => $cv['telephone'],
                "county" => $cv['county'],
                "city" => $cv['city'],
                "positions" => str_replace(",", ", ", $cv['positions']),
                "offer" => '<a href="https://mumi.hu/hu/allas/'.$offer['slug'].'" target="_blank" title="Hírdetés">'.$firm_name.$offer['title'].' ('.$offer['city'].')'.'</a>',
                "cv_url" => ($cv['cv_url']) ? '<a href="'.$cv['cv_url'].'" target="_blank" title="Megtekintés"><i class="fa fa-file-text"></i></a>' : '',
                "created_at" => $cv['created_at'],
                "updated_at" => $cv['updated_at'],
                "settings" => '<a href="edit.php?e='.$cv['id'].'" target="_blank" title="Szerkesztés"><i class="fa fa-edit"></i></a>'
            ];
        }
        
        header('Content-Type: application/json');
        
        $response = [
            "draw" => $draw,
            "recordsTotal" => (int)$results['total'],
            "recordsFiltered" => (int)$results['filtered'],
            "data" => $data
        ];

        // Debug információ logolása
        error_log("DataTables Response: " . print_r($response, true));
        
        echo json_encode($response);
        exit;
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Database error occurred']);
    }
} else {
    // Excel export
    $filename = date('Ymdhis'). '_csv_export.xlsx';
    
    try {
        // Get all data without pagination for Excel
        $results = getCvs(
            $con, 
            $cities, 
            $categories, 
            $counties, 
            $name, 
            $email, 
            $telephone,  // Itt volt egy elírás (telephone helyett telephone)
            $birthday_over, 
            $birthday_down, 
            $traveling, 
            $foreigner, 
            $languages, 
            $drivings, 
            $sources, 
            $cv, 
            $status, 
            $offers, 
            '', // search
            0,  // orderColumn
            'DESC', // orderDir
            0,  // start
            999999  // length - nagy szám a limit-hez
        );
        
        if(!empty($results['data'])) {
            $data = [];
            foreach($results['data'] as $cv) {
                $offer = getOffer($con, $cv['offer_id']);
                $firm = null;
                if($offer) {
                    $firm = getFirm($con, $offer['firm_id']);
                }
                $firm_name = (isset($firm['name'])) ? $firm['name'].' - ' : '';
                
                $data[] = [
                    "id" => $cv['id'],
                    "name" => $cv['name'],
                    "birthday" => $cv['birthday'],
                    "email" => $cv['email'],
                    "telephone" => $cv['telephone'],
                    "county" => $cv['county'],
                    "city" => $cv['city'],
                    "positions" => $cv['positions'],
                    "source" => $cv['source'],
                    "driving_license" => $cv['driving_license'],
                    "languages" => $cv['languages'],
                    "traveling" => ($cv['traveling'] == 1) ? "Igen" : "Nem",
                    "traveling_km" => $cv['traveling_km'],
                    "foreigner" => ($cv['foreigner'] == 1) ? "Igen" : "Nem",
                    "where_foreign" => $cv['where_foreign'],
                    "cv_url" => $cv['cv_url'],
                    "created_at" => $cv['created_at'],
                    "updated_at" => $cv['updated_at'],
                    "comment" => $cv['comment'],
                    "offer" => $offer ? $firm_name.$offer['title'] : '',
                    "offer_url" => $offer ? 'https://mumi.hu/hu/allas/'.$offer['slug'] : ''
                ];
            }

            $objPHPExcel = new PHPExcel();
            $activeSheet = $objPHPExcel->getActiveSheet();

            // Excel fejlécek beállítása
            $headers = [
                'A1' => 'Felhasználó id',
                'B1' => 'Név',
                'C1' => 'Születési dátum',
                'D1' => 'Email',
                'E1' => 'Telefonszám',
                'F1' => 'Megye',
                'G1' => 'Város',
                'H1' => 'Pozíciók',
                'I1' => 'Forrás',
                'J1' => 'Vezetői engedélyek',
                'K1' => 'Nyelvek',
                'L1' => 'Utazna-e',
                'M1' => 'Km',
                'N1' => 'Külföld',
                'O1' => 'Külföldre hová',
                'P1' => 'CV url',
                'Q1' => 'Létrehozás dátuma',
                'R1' => 'Módosítás dátuma',
                'S1' => 'Megjegyzés',
                'T1' => 'Hírdetés',
                'U1' => 'Hírdetés url'
            ];

            foreach ($headers as $cell => $value) {
                $activeSheet->SetCellValue($cell, $value);
            }

            // Adatok feltöltése
            $current_cell = 2;
            foreach ($data as $row) {
                $objPHPExcel->getActiveSheet()->fromArray($row, null, 'A'.$current_cell);
                $current_cell++;
            }

            try {
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->save('csv/'.$filename);
                $link = "https://mumi.hu/employees/csv/".$filename;
            } catch (Exception $e) {
                error_log("Excel write error: " . $e->getMessage());
                throw new Exception('Failed to write Excel file');
            }
        } else {
            $link = '';
        }

        header('Content-Type: application/json');
        echo json_encode([
            "link" => $link,
            'count' => isset($results['data']) ? count($results['data']) : 0
        ]);

    } catch (Exception $e) {
        error_log("Excel generation error: " . $e->getMessage());
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json');
        echo json_encode([
            "link" => '',
            'count' => 0,
            'error' => 'Excel generation failed: ' . $e->getMessage()
        ]);
    }
}

function getCvs($con, $cities, $categories, $counties, $name, $email, $telephone, 
                $birthday_over, $birthday_down, $traveling, $foreigner, $languages, 
                $drivings, $sources, $cv, $status, $offers, $search, $orderColumn, 
                $orderDir, $start, $length) {
    
    $params = array();
    $columns = [
        'id', 'status', 'name', 'email', 'telephone', 'county', 
        'city', 'positions', 'source', 'cv_url', 'created_at', 'updated_at'
    ];
    
    // Alap lekérdezés ahol az offer_id nem null
    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM query_employees WHERE offer_id IS NOT NULL";
    
    // Státusz szűrés
    if($status !== null && $status !== '') {
        $sql .= " AND status = :status";
        $params[':status'] = $status;
    }

    // Név szűrés
    if($name !== null && $name !== '') {
        $sql .= " AND name LIKE :name";
        $params[':name'] = '%'.$name.'%';
    }

    // Email szűrés
    if($email !== null && $email !== '') {
        $sql .= " AND email LIKE :email";
        $params[':email'] = '%'.$email.'%';
    }

    // Telefonszám szűrés
    if($telephone !== null && $telephone !== '') {
        $sql .= " AND telephone LIKE :telephone";
        $params[':telephone'] = '%'.$telephone.'%';
    }

    // Születési dátum intervallum szűrés
    if($birthday_over !== null && $birthday_over !== '') {
        $sql .= " AND birthday > :birthday_over";
        $params[':birthday_over'] = $birthday_over;
    }

    if($birthday_down !== null && $birthday_down !== '') {
        $sql .= " AND birthday < :birthday_down";
        $params[':birthday_down'] = $birthday_down;
    }

    // Utazás szűrés
    if($traveling !== null && $traveling !== '') {
        $sql .= " AND traveling = :traveling";
        $params[':traveling'] = ($traveling == 1) ? 1 : 0;
    }

    // Külföldi munka szűrés
    if($foreigner !== null && $foreigner !== '') {
        $sql .= " AND foreigner = :foreigner";
        $params[':foreigner'] = ($foreigner == 1) ? 1 : 0;
    }

    // Forrás szűrés
    if($sources !== null && $sources !== '') {
        $sql .= " AND source = :sources";
        $params[':sources'] = $sources;
    }

    // CV szűrés
    if($cv !== null && $cv !== '') {
        if($cv == 1) {
            $sql .= " AND cv_url != ''";
        } else {
            $sql .= " AND cv_url = ''";
        }
    }

    // Hirdetés (offer) szűrés
    if(is_array($offers) && !empty($offers)) {
        $sql .= " AND offer_id IN (".implode(", ", array_map('intval', $offers)).")";
    }

    // Jogosítvány szűrés
    if(is_array($drivings) && !empty($drivings)) {
        $drivingConditions = [];
        foreach($drivings as $key => $driving) {
            $paramName = ':driving'.$key;
            $drivingConditions[] = "driving_license LIKE ".$paramName;
            $params[$paramName] = '%'.$driving.'%';
        }
        $sql .= " AND (".implode(" OR ", $drivingConditions).")";
    }

    // Nyelv szűrés
    if(is_array($languages) && !empty($languages)) {
        $languageConditions = [];
        foreach($languages as $key => $language) {
            $paramName = ':language'.$key;
            $languageConditions[] = "language LIKE ".$paramName;
            $params[$paramName] = '%'.$language.'%';
        }
        $sql .= " AND (".implode(" OR ", $languageConditions).")";
    }

    // Kategória szűrés
    if(is_array($categories) && !empty($categories)) {
        $categoryConditions = [];
        foreach($categories as $key => $category) {
            $paramName = ':category'.$key;
            $categoryConditions[] = "positions LIKE ".$paramName;
            $params[$paramName] = '%'.$category.'%';
        }
        $sql .= " AND (".implode(" OR ", $categoryConditions).")";
    }

    // Város szűrés
    if(is_array($cities) && !empty($cities)) {
        $cityConditions = [];
        foreach($cities as $key => $city) {
            $paramName = ':city'.$key;
            $cityConditions[] = "city LIKE ".$paramName;
            $params[$paramName] = '%'.$city.'%';
        }
        $sql .= " AND (".implode(" OR ", $cityConditions).")";
    }

    // Megye szűrés
    if(is_array($counties) && !empty($counties)) {
        $countyConditions = [];
        foreach($counties as $key => $county) {
            $paramName = ':county'.$key;
            $countyConditions[] = "county LIKE ".$paramName;
            $params[$paramName] = '%'.$county.'%';
        }
        $sql .= " AND (".implode(" OR ", $countyConditions).")";
    }

    // DataTables keresés
    if(!empty($search)) {
        $sql .= " AND (name LIKE :search OR email LIKE :search OR 
                      telephone LIKE :search OR city LIKE :search)";
        $params[':search'] = '%'.$search.'%';
    }

    // Számoljuk meg először a szűrt rekordok számát LIMIT nélkül
    $countSql = preg_replace('/SELECT SQL_CALC_FOUND_ROWS \*/', 'SELECT COUNT(*) as count', $sql);
    $stmtCount = $con->prepare($countSql);
    foreach($params as $key => &$val) {
        $stmtCount->bindValue($key, $val);
    }
    $stmtCount->execute();
    $filteredCount = (int)$stmtCount->fetchColumn();

    // Rendezés
    if(isset($columns[$orderColumn])) {
        $sql .= " ORDER BY ".$columns[$orderColumn]." ".$orderDir;
    } else {
        $sql .= " ORDER BY id DESC";
    }

    // Lapozás
    $sql .= " LIMIT :start, :length";
    $params[':start'] = (int)$start;
    $params[':length'] = (int)$length;

    try {
        // Debug információ
        error_log("SQL Query: " . $sql);
        error_log("Parameters: " . print_r($params, true));

        // Lekérdezés végrehajtása
        $stmt = $con->prepare($sql);
        
        // Paraméterek kötése
        foreach($params as $key => &$val) {
            if(strpos($key, 'start') !== false || strpos($key, 'length') !== false) {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val);
            }
        }
        
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'total' => $filteredCount,  // Most a szűrt rekordok számát használjuk mindkét értéknek
            'filtered' => $filteredCount
        ];

    } catch (PDOException $e) {
        error_log("Database Error in getCvs: " . $e->getMessage());
        throw $e;
    }
}

function getOffer($con, $offer_id) {
    if (!$offer_id) return null;
    
    $sql = "
        SELECT *, (SELECT d.value FROM offer_dictionary_relation odr 
                  INNER JOIN dictionary d ON d.id=odr.dictionary_id 
                  WHERE d.dictionary_type=9 AND odr.offer_id=o.id LIMIT 1) as 'city'
        FROM offer o
        WHERE o.id=:offer_id";
    
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':offer_id', $offer_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getFirm($con, $firm_id) {
    if (!$firm_id) return null;
    
    $sql = "
        SELECT *
        FROM firm
        WHERE id=:firm_id";
    
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':firm_id', $firm_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}