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
header('Content-Type: application/json; charset=utf-8');
ini_set('memory_limit', '-1');
ini_set('date.timezone', 'Europe/Budapest');
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);

$id = (int) $_POST['id'];


$data = [];


$data['id'] = $id; 
$data['name'] = $_POST['name'];
$data['status'] = $_POST['status'];
$data['email'] = $_POST['email'];
$data['birthday'] = $_POST['birthday'] ?: NULL;
$data['telephone'] = $_POST['telephone'];
$data['comment'] = $_POST['comment'];
$data['county'] = (is_array($_POST['counties'])) ? implode(",", $_POST['counties']) : $_POST['counties'];
$data['city'] = (is_array($_POST['states'])) ? implode(",", $_POST['states']) : $_POST['states'];
$data['positions'] = (is_array($_POST['categories'])) ? implode(",", $_POST['categories']) : $_POST['categories'];
$data['driving_license'] = (is_array($_POST['driving_license'])) ? implode(",", $_POST['driving_license']) : $_POST['driving_license'];
$data['language'] = (is_array($_POST['languages'])) ? implode(",", $_POST['languages']) : $_POST['languages'];
$data['source'] = (is_array($_POST['sources'])) ? implode(",", $_POST['sources']) : $_POST['sources'];
$data['traveling'] =  $_POST['traveling'] ?: 0;
$data['traveling_km'] = $_POST['traveling_km'] ?: 0;
$data['foreigner'] = (int) $_POST['foreign'] ?: 0;
$data['where_foreign'] = $_POST['where_foreign'];
$data['cv_url'] = $_POST['cv_url'];
$data['updater_id'] = $_SESSION['query_user_id'] ?? NULL;
$data['updated_at'] = date('Y-m-d H:i:s');


$employee = findEmployee($con, $id);

if(!empty($data['email']) && $data['email'] !== $employee['email']) {

   $exist = findEmail($con, $data['email']);

   if(isset($exist['email'])) {
      $return_data = json_encode([
      "Létezik már az e-mail cím az adatbázisban."
      ]);

      echo $return_data;
      return;
   }

}

$save = saveCv($con, $data);

$return_data = json_encode(
"ok"
);

echo $return_data;


function saveCv($con, $data) {

   $sql = "UPDATE query_employees SET name=:name, email=:email, birthday=:birthday, telephone=:telephone, county=:county, city=:city, positions=:positions, traveling=:traveling, traveling_km=:traveling_km, foreigner=:foreigner, where_foreign=:where_foreign, cv_url=:cv_url, updated_at=:updated_at, updater_id=:updater_id, driving_license=:driving_license, language=:language, source=:source, comment=:comment, status=:status WHERE id=:id";

   $stmt = $con->prepare($sql);

   $stmt->execute($data);
    
   return $stmt;

}

function findEmployee($con, $id) {
   $sql="
        SELECT * FROM query_employees
      WHERE id = '".$id."'";

      $stmt = $con->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

   return $result;
}

function findEmail($con, $email) {
   $sql="
        SELECT * FROM query_employees
      WHERE email = '".$email."'";

      $stmt = $con->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

   return $result;
}
