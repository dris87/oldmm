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


// Budapest kerületek kezelése
if($_POST['states']) {
    if(in_array('Budapest', $_POST['states'], true)) {
        $budapestAreas = [
            "Budapest VI. kerület", "Budapest I. kerület", "Budapest II. kerület", 
            "Budapest III. kerület", "Budapest IV. kerület", "Budapest V. kerület", 
            "Budapest VII. kerület", "Budapest VIII. kerület", "Budapest IX. kerület", 
            "Budapest X. kerület", "Budapest XI. kerület", "Budapest XII. kerület", 
            "Budapest XIII. kerület", "Budapest XIV. kerület", "Budapest XV. kerület", 
            "Budapest XVI. kerület", "Budapest XVII. kerület", "Budapest XVIII. kerület", 
            "Budapest XIX. kerület", "Budapest XX. kerület", "Budapest XXI. kerület", 
            "Budapest XXII. kerület", "Budapest XXIII. kerület", "Budapest"
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
$language_level = !empty($_POST['language_level']) ? $_POST['language_level'] : NULL;
$cv = isset($_POST['cv']) ? $_POST['cv'] : NULL;

// Itt jönne a getCvs függvény definíciója...

// Adatok lekérése
try {
    $csvs = getCvs($con, $cities, $categories, $counties, $name, $email, $telephone, 
        $birthday_over, $birthday_down, $traveling, $foreigner, $languages, 
        $drivings, $sources, $cv, $status);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Failed to fetch data']);
    exit;
}

// DataTables válasz
if($_GET['m'] === "q") {
    header('Content-Type: application/json');
    $data = [];
    
    foreach($csvs as $cv) {
        $deleteText = '<br><a href="delete.php?e='.$cv['id'].'" onclick="return confirm(\'Biztos törölni szeretné '.$cv['id'].' - '.$cv['name'].' cv-t?\');">Törlés</a>';
        $selectText = '<select onchange="statusChange(this, '.$cv['id'].')" id="change-status'.$cv['id'].'" data-id="'.$cv['id'].'" >';
        foreach($statusArray as $key => $status) {
            $selected = ($cv['status'] == $key ) ? 'selected' : '';
            $selectText .= '<option value="'.$key.'" '.$selected.'>'.$status.'</option>';
        }
        $selectText .= "</select>";
        
        $data[] = [
            "id" => $cv['id'],
            "status" => $selectText,
            "name" => '<a href="edit.php?e='.$cv['id'].'" target="_blank" style="text-decoration:none;" title="Szerkesztés">'.$cv['name'].'</a>',
            "birthday" => $cv['birthday'],
            "email" => $cv['email'],
            "telephone" => $cv['telephone'],
            "county" => $cv['county'],
            "city" => $cv['city'],
            "positions" => str_replace(",", ", ", $cv['positions']),
            "source" => $cv['source'],
            "driving_license" => $cv['driving_license'],
            "languages" => $cv['languages'],
            "language_level" => $cv['language_level'],
            "traveling" => ($cv['traveling'] == 1) ? "Igen" : "Nem",
            "traveling_km" => $cv['traveling_km'],
            "foreigner" => ($cv['foreigner'] == 1) ? "Igen" : "Nem",
            "where_foreign" => $cv['where_foreign'],
            "cv_url" => ($cv['cv_url']) ? '<a href="'.$cv['cv_url'].'" target="_blank" title="Megtekintés"><i class="fa fa-file-text"></i></a>' : '',
            "created_at" => $cv['created_at'],
            "updated_at" => $cv['updated_at'],
            "settings" => '<a href="edit.php?e='.$cv['id'].'" target="_blank" title="Szerkesztés"><i class="fa fa-edit"></i></a>',
        ];
    }

    $response = [
        "data" => $data,
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => count($data),
        "recordsFiltered" => count($data)
    ];

    echo json_encode($response);
    error_log("Response data: " . print_r($response, true));
    exit;
}

// Excel export
$filename = date('Ymdhis'). '_csv_export.xlsx';
if(count($csvs) > 0) {
    $data = [];
    foreach($csvs as $cv) {
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
            "comment" => $cv['comment']
        ];
    }

    try {
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
            'S1' => 'Megjegyzés'
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

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('csv/'.$filename);
        $link = "https://mumi.hu/employees/csv/".$filename;
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            "link" => '',
            'count' => 0,
            'error' => 'Excel generation failed'
        ]);
        exit;
    }
} else {
    $link = '';
}

$count = count($csvs);

header('Content-Type: application/json');
echo json_encode([
    "link" => $link,
    'count' => $count
]);
exit;

function getCvs($con, $cities, $categories, $counties, $name, $email, $telephone, $birthday_over, $birthday_down, $traveling, $foreigner, $languages, $drivings, $sources, $cv, $status) {

    $params = array();
    $sql = "SELECT * FROM query_employees WHERE offer_id IS NULL";

    // Csak akkor adjuk hozzá a feltételeket, ha van értékük
    if ($status !== null && $status !== '') {
        $sql .= " AND status = :status";
        $params[':status'] = $status;
    }

    if ($name !== null && $name !== '') {
        $sql .= " AND name LIKE :name";
        $params[':name'] = '%' . $name . '%';
    }

    if ($email !== null && $email !== '') {
        $sql .= " AND email LIKE :email";
        $params[':email'] = '%' . $email . '%';
    }

    if ($telephone !== null && $telephone !== '') {
        $sql .= " AND telephone LIKE :telephone";
        $params[':telephone'] = '%' . $telephone . '%';
    }

    if ($birthday_over !== null && $birthday_over !== '') {
        $sql .= " AND birthday > :birthday_over";
        $params[':birthday_over'] = $birthday_over;
    }

    if ($birthday_down !== null && $birthday_down !== '') {
        $sql .= " AND birthday < :birthday_down";
        $params[':birthday_down'] = $birthday_down;
    }

    if ($traveling !== null && $traveling !== '') {
        $sql .= " AND traveling = :traveling";
        $params[':traveling'] = ($traveling == 1) ? 1 : 0;
    }

    if ($foreigner !== null && $foreigner !== '') {
        $sql .= " AND foreigner = :foreigner";
        $params[':foreigner'] = ($foreigner == 1) ? 1 : 0;
    }

    if ($sources !== null && $sources !== '') {
        $sql .= " AND source = :sources";
        $params[':sources'] = $sources;
    }

    if ($cv !== null && $cv !== '') {
        if ($cv == 1) {
            $sql .= " AND cv_url != ''";
        } else {
            $sql .= " AND cv_url = ''";
        }
    }

    // Tömbök kezelése - csak ha nem üresek
    if (is_array($drivings) && !empty($drivings)) {
        $driving_conditions = array();
        foreach ($drivings as $key => $driving) {
            $param_name = ':driving' . $key;
            $driving_conditions[] = "driving_license LIKE " . $param_name;
            $params[$param_name] = '%' . $driving . '%';
        }
        $sql .= " AND (" . implode(" OR ", $driving_conditions) . ")";
    }

    if (is_array($languages) && !empty($languages)) {
        $language_conditions = array();
        foreach ($languages as $key => $language) {
            $param_name = ':language' . $key;
            $language_conditions[] = "language LIKE " . $param_name;
            $params[$param_name] = '%' . $language . '%';
        }
        $sql .= " AND (" . implode(" OR ", $language_conditions) . ")";
    }

    if (is_array($categories) && !empty($categories)) {
        $category_conditions = array();
        foreach ($categories as $key => $category) {
            $param_name = ':category' . $key;
            $category_conditions[] = "positions LIKE " . $param_name;
            $params[$param_name] = '%' . $category . '%';
        }
        $sql .= " AND (" . implode(" OR ", $category_conditions) . ")";
    }

    if (is_array($cities) && !empty($cities)) {
        $city_conditions = array();
        foreach ($cities as $key => $city) {
            $param_name = ':city' . $key;
            $city_conditions[] = "city LIKE " . $param_name;
            $params[$param_name] = '%' . $city . '%';
        }
        $sql .= " AND (" . implode(" OR ", $city_conditions) . ")";
    }

    if (is_array($counties) && !empty($counties)) {
        $county_conditions = array();
        foreach ($counties as $key => $county) {
            $param_name = ':county' . $key;
            $county_conditions[] = "county LIKE " . $param_name;
            $params[$param_name] = '%' . $county . '%';
        }
        $sql .= " AND (" . implode(" OR ", $county_conditions) . ")";
    }

    $sql .= " ORDER BY id DESC";


    try {
        $stmt = $con->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $results;
    } catch (PDOException $e) {

        throw $e;
    }
}
