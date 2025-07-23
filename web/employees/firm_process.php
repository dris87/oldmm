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
ini_set('display_errors', 1);



$firms_post = $_POST['firms'] ?: NULL;


$firms = getFirms($con, $firms_post);

$data = [];
if($_GET['m'] === "q") {
header('Content-Type: application/json');
    if($firms) {
   foreach($firms as $firm) {

      $selectText = ($firm['status'] == 1) ? "Aktív" : "Inaktív";

      $location = ($firm['location_id']) ? getLocation($con, $firm['location_id']) : '';
      $user = getUser($con, $firm['id']);
      $postalLocation = ($firm['postal_location_id']) ? getLocation($con, $firm['postal_location_id']) : '';

      $locationText = ($location) ? $location['zip']." ".$location['city']." (".$location['county'].")" : '';
      $postalLocationText = ($postalLocation) ? $postalLocation['zip']." ".$postalLocation['city']." (".$postalLocation['county'].")" : '';
      $info = '<a data-toggle="modal" data-id="ISBN-001122" title="Add this item" class="open-AddBookDialog btn btn-primary" href="#addBookDialog">test</a>';
      $data[] = [
         "id" => $firm['id'],
         "status" => $selectText,
         "firm_name" => $firm['name'],
         "representative" => $firm['representative'],
         "email" => ($user) ? $user['email'] : '',
         "phone_number" => ($user) ? $user['phone_number'] : '',
         "position" => ($user) ? $user['position'] : '',
         "firm_name_long" => $firm['name_long'] ?: '',
         "tax_number" => $firm['tax_number'],
         "location" => $locationText." ".$street." ".$street_number." ".$floor." ".$door_number,
         "postal_location" => $postalLocationText." ".$posta_street." ".$posta_street_number." ".$posta_floor." ".$posta_door_number,
         "position" => ($user) ?  $user['position'] : '',
         "u_name" => ($user) ? $user['first_name']." ".$user['last_name'] : '',
         "street" => $firm['street'],
         "street_number" => $firm['street_number'],
         "floor" => $firm['floor'],
         "door_number" => $firm['door_number'],
         "postal_street" => $firm['street'],
         "postal_street_number" => $firm['street_number'],
         "postal_floor" => $firm['floor'],
         "postal_door_number" => $firm['door_number'],
         "web_page_url" => $firm['web_page_url'],
         "created_at" => $firm['created_at'],
         "updated_at" => $firm['updated_at'],
         'info' => $info
      ];

   }
   }

   $return_data = json_encode([
      "records" => $data,
      'count' => count($data)
   ]);
   echo $return_data;

}


function getFirms($con, $firm_id_array) {

    if(!empty($firm_id_array)) {
    $firm_ids = implode(", ",$firm_id_array);
    
    
    $sql="
        SELECT *
        FROM firm f
        WHERE f.id IN (".$firm_ids.")";
         
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
        //PDO::FETCH_ASSOC
   }

   return "";
}

function getLocation($con, $id) {

    $sql="
        SELECT (SELECT value FROM dictionary WHERE id=dl.zip_id) as 'zip',(SELECT value FROM dictionary WHERE id=dl.county_id) as 'county', (SELECT value FROM dictionary WHERE id=dl.city_id) as 'city'
        FROM dic_location dl
        WHERE dl.id=$id";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
        //PDO::FETCH_ASSOC

   return "";
}

function getUser($con, $id) {

    $sql="
        SELECT u.first_name, u.last_name, u.email,(SELECT value FROM dictionary WHERE id=u.position_id) as 'position', u.phone_number
        FROM user u
        WHERE u.firm_id=$id";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
        //PDO::FETCH_ASSOC

   return "";
}