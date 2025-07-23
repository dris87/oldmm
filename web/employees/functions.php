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

if($_GET['action'] === "email") {
   if(!empty($_POST['email'])) {
      $exist = findEmail($con, $_POST['email']);

      if(isset($exist['email'])) {
         $return_data = json_encode('error');
         echo $return_data;
         return;
      }
   }
}

if($_GET['action'] === "telephone") {
   if(!empty($_POST['telephone'])) {
      $exist = findTelephone($con, $_POST['telephone']);

      if(isset($exist['telephone'])) {
         $return_data = json_encode('error');
         echo $return_data;
         return;
      }
   }
}

if($_GET['action'] === "status-change-salesman") {

   if(!empty($_POST['id']) && !empty($_POST['value'])) {
      if(updateSalesmanStatus($con, $_POST['id'], $_POST['value'])){
         $return_data = json_encode('ok');
      } else {
         $return_data = json_encode('error');
      }

      echo $return_data;
      return;
   }
}

if($_GET['action'] === "status-change") {

   if(!empty($_POST['id']) && !empty($_POST['value'])) {
      if(updateStatus($con, $_POST['id'], $_POST['value'])){
         $return_data = json_encode('ok');
      } else {
         $return_data = json_encode('error');
      }

      echo $return_data;
      return;
   }
}


return;


function findEmail($con, $email) {
   $sql="
        SELECT * FROM query_employees
      WHERE email = '".$email."' AND offer_id IS NULL";

      $stmt = $con->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

   return $result;
}

function findTelephone($con, $telephone) {


   $sql="
        SELECT * FROM query_employees
      WHERE telephone = '".$telephone."' AND offer_id IS NULL";
  
      $stmt = $con->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

   return $result;
}

function updateStatus($con, $id, $value) {


   $sql = "UPDATE query_employees SET status=:status, updater_id=:updater_id, updated_at=:updated_at WHERE id=:id";

   $stmt = $con->prepare($sql);

   $stmt->execute(["id" => $id, "status" => $value, 'updater_id' => $_SESSION['query_user_id'], 'updated_at' => date('Y-m-d H:i:s')]);
    
   return $stmt;

}

function updateSalesmanStatus($con, $id, $value) {


   $sql = "UPDATE salesman_registrations SET status=:status, updated_at=:updated_at WHERE id=:id";

   $stmt = $con->prepare($sql);

   $stmt->execute(["id" => $id, "status" => $value, 'updated_at' => date('Y-m-d H:i:s')]);
    
   return $stmt;

}