<?php
session_start();

require_once 'PHPExcel/Classes/PHPExcel.php';
require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';

ini_set('memory_limit', '-1');
ini_set('date.timezone', 'Europe/Budapest');
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);

if(!$_SESSION['query_user']) {
   return;
}

$categories = (is_array($_POST['categories'])) ? implode(",", $_POST['categories']) : NULL;
$counties = (is_array($_POST['counties'])) ? implode(",", $_POST['counties']) : NULL;
$created_over = $_POST['created_over'] ?: NULL;
$created_down = $_POST['created_down'] ?: NULL;

if(in_array('10354', $_POST['states'], true)) {
   $budapestAreas=[
      10331, 
      10332, 
      10333, 
      10334, 
      10335, 
      10336, 
      10337, 
      10338, 
      10339, 
      10340, 
      10341, 
      10342, 
      10343, 
      10344, 
      10345, 
      10346, 
      10347, 
      10348, 
      10349, 
      10350, 
      10351, 
      10352, 
      10353, 
      10354
   ];

   $_POST['states'] = array_merge($_POST['states'], $budapestAreas);

} 

$cities = (is_array($_POST['states'])) ? implode(",", $_POST['states']) : NULL;

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



$csvs = getCvs($con, $cities, $categories, $counties, $created_over, $created_down);
$filename = date('Ymdhis'). '_csv_export.xlsx';
if(count($csvs) > 0) {
   $objPHPExcel = new PHPExcel();
   $activeSheet = $objPHPExcel->getActiveSheet();


   //output headers
   $activeSheet->SetCellValue('A1', 'Felhasználó id');
   $activeSheet->SetCellValue('B1', 'Vezetéknév');
   $activeSheet->SetCellValue('C1', 'Keresztnév');
   $activeSheet->SetCellValue('D1', 'Email');
   $activeSheet->SetCellValue('E1', 'Telefonszám');
   $activeSheet->SetCellValue('F1', 'Registráció dátuma');
   $activeSheet->SetCellValue('G1', 'Irányítószám');
   $activeSheet->SetCellValue('H1', 'Megye');
   $activeSheet->SetCellValue('I1', 'Város');
   $activeSheet->SetCellValue('J1', 'Keresett pozíciók');
   $activeSheet->SetCellValue('K1', 'CV link');
   //output valuess
   $current_cell = 2;
   foreach ($csvs as $csv) {
      $objPHPExcel->getActiveSheet()->fromArray($csv, null, 'A'.$current_cell);
      $current_cell++;
   }
   $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
   $objWriter->save('csv/'.$filename);
}

$count = count($csvs);

$link = (count($csvs) > 0) ? "https://mumi.hu/query/csv/".$filename : '';
header('Content-Type: application/json');
$return_data = json_encode([
"link" => $link,
'count' => $count
]);

echo $return_data;

function getCvs($con, $cities, $categories, $counties, $created_over, $created_down) {

     $sql="
        SELECT u.id, u.first_name, u.last_name, u.email, u.phone_number, u.created_at, (SELECT value FROM dictionary d WHERE d.id=dl.zip_id) as 'zip',  (SELECT value FROM dictionary d WHERE d.id=dl.county_id) as 'county', (SELECT value FROM dictionary d WHERE d.id=dl.city_id) as 'city', (SELECT GROUP_CONCAT(d.value SEPARATOR ', ') FROM employee_cv_dictionary_relation as ecdr INNER JOIN dictionary d ON d.id=ecdr.dictionary_id WHERE ecdr.employee_cv_id=ec.id AND d.dictionary_type=5) as 'categories', CONCAT('https://mumi.hu/app/admin/generate/', ec.id) as 'link' FROM `employee_cv` ec
      INNER JOIN user u ON u.id=ec.employee_id
      INNER JOIN dic_location dl ON dl.id=u.location_id
      WHERE ec.status=1";

   if(count($categories) > 0) {
      $sql = $sql." AND (SELECT COUNT(*) FROM employee_cv_dictionary_relation as ecdr WHERE ecdr.employee_cv_id=ec.id AND ecdr.dictionary_id IN (".$categories.")) > 0";
   }

   if(count($cities) > 0) {
      $sql = $sql." AND dl.city_id IN (".$cities.")";
   }

   if(count($counties) > 0) {
      $sql = $sql." AND dl.county_id IN (".$counties.")";
   }

   if($created_over !== null) {
         $sql = $sql." AND u.created_at > '".$created_over."' ";
   }

   if($created_down !== null) {
         $sql = $sql." AND u.created_at < '".$created_down."' ";
   }

    $stmt = $con->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;

}

