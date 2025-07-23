<?php

ini_set('date.timezone', 'Europe/Budapest');
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

$con = connectDB('localhost', 'c1_web', 'c1_web', '5DpzFiY@5');

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
      $exist = findTelephone($con, $_POST['telephone'], $_POST['offer_id']);

      if(isset($exist['telephone'])) {
         $return_data = json_encode('error');
         echo $return_data;
         return;
      }
   }
}


return;




function connectDB($host, $db="tapa", $user, $pwd){
      $dsn = 'mysql:host='.$host.';dbname='.$db.';';
      $un = $user;
      $pwd = $pwd;
        $con = new PDO($dsn, $un, $pwd);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $con->exec("SET CHARACTER SET utf8");
        $con->exec("SET NAMES utf8");
        return $con;
}

function findEmail($con, $email) {
   $sql="
        SELECT * FROM query_employees
      WHERE email = '".$email."' AND offer_id IS NULL";

      $stmt = $con->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

   return $result;
}

function findTelephone($con, $telephone, $offer_id) {


   $sql="
        SELECT * FROM query_employees
      WHERE telephone = '".$telephone."' AND offer_id=".$offer_id;
  
      $stmt = $con->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

   return $result;
}
