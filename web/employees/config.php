<?php

$sources = [
    "Egyéb", 
    "Facebook", 
    "Jooble", 
    "Karrier", 
    "Regisztrált", 
    "Regisztrált hiányos", 
    "Ügyfélszolgálat",
    "Direkt jelentkező",
    "mumi"
];

$levels = [
    "alapfok",
    "középfok",
    'felsőfok',
];

$statusArray = [
	1 => "Új jelentkezés", 
	2 => "CRM",
	3 => "Elhelyezkedett",
    4 => "Kiküldve",
    5 => "Letiltva",
    6 => "Rögzítve", 
    7 => "Nem releváns", 
    8 => "Nem aktuális", 
    9 => "Álláskereső",
    10 => "NVF",
    11 => "CV-t várok"
];

$salesmanStatusArray = [
    1 => "Új kapcsolatfelvétel", 
    2 => "Nem vette fel", 
    3 => "Sikeres kapcsolatfelvétel", 
    4 => "Sikertelen kapcsolatfelvétel", 
];


$con = connectDB('localhost', 'c1_web', 'c1_web', '5DpzFiY@5');
//$con = connectDB('127.0.0.1', 'mumi', 'root', 'test');

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