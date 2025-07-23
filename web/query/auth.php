<?php
session_start();
ini_set('memory_limit', '-1');
ini_set('date.timezone', 'Europe/Budapest');
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);

$con = connectDB('localhost', 'c1_web', 'c1_web', '5DpzFiY@5');

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



$name =  $_POST['logName'];
$paswdr = $_POST['paswdr'];


if(!isset($name, $paswdr)) {
    echo "Helytelen bejelentkezés.";
    return;
}

$sql="SELECT *
      FROM query_users u
      WHERE u.name='".$name."'";

$stmt = $con->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(!$result || md5($paswdr) !== $result[0]['password']) {
    echo "Helytelen bejelentkezés.";
    return;
}

$_SESSION['query_user'] = $name;

header('Content-Type: application/json');
$return_data = json_encode(
"ok"
);

echo $return_data;
return;
