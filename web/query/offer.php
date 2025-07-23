<?php
session_start();

require_once 'PHPExcel/Classes/PHPExcel.php';
require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';

ini_set('memory_limit', '-1');
ini_set('date.timezone', 'Europe/Budapest');
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

if(!$_SESSION['query_user']) {
   return;
}

$yesterday = date('Y-m-d',strtotime("-1 days"));

$date = ($_POST['date']) ? $_POST['date'] : $yesterday;


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



$offers = getOffers($con, $date);

$filename = date('Ymdhis'). '_offers_csv_export.xlsx';
if(count($offers) > 0) {
   $objPHPExcel = new PHPExcel();
   $activeSheet = $objPHPExcel->getActiveSheet();


   //output headers
   $activeSheet->SetCellValue('A1', 'Hirdetés id');
   $activeSheet->SetCellValue('B1', 'Cég');
   $activeSheet->SetCellValue('C1', 'Hirdetés címe');
   $activeSheet->SetCellValue('D1', 'Lejárati dátuma');
   $activeSheet->SetCellValue('E1', 'Admin link');
   $activeSheet->SetCellValue('F1', 'Hirdetés link');

   //output valuess
   $current_cell = 2;
   foreach ($offers as $csv) {
      $objPHPExcel->getActiveSheet()->fromArray($csv, null, 'A'.$current_cell);
      $current_cell++;
   }
   $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
   $objWriter->save('csv/'.$filename);
}

$count = count($offers);

$link = (count($offers) > 0) ? "https://mumi.hu/query/csv/".$filename : '';
header('Content-Type: application/json');
$return_data = json_encode([
"link" => $link,
'count' => $count
]);

echo $return_data;

function getOffers($con, $date) {

     $sql='SELECT o.id, f.name, o.title, o.expire_date, CONCAT("https://mumi.hu/admin/common/core/offer-offer/", o.id, "/edit") as "admin_link", CONCAT("https://mumi.hu/hu/allas/", o.slug) as "link" FROM offer o LEFT JOIN firm f ON o.firm_id=f.id
      WHERE o.expire_date="'.$date.'"';

    $stmt = $con->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;

}

