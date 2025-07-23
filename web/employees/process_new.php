<?php
session_set_cookie_params('86400');
ini_set("session.gc_maxlifetime", 86400);
ini_set("session.cookie_lifetime", 86400);
session_start([
    'cookie_lifetime' => 86400,
]);

require_once 'PHPExcel/Classes/PHPExcel.php';
require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';
require_once 'config.php';

ini_set('memory_limit', '-1');
ini_set('date.timezone', 'Europe/Budapest');
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

if(!$_SESSION['query_user']) {
   return;
}


if($_POST['states']) {
   if(in_array('Budapest', $_POST['states'], true)) {
      $budapestAreas=[
         "Budapest VI. kerület", "Budapest I. kerület", "Budapest II. kerület", "Budapest III. kerület", "Budapest IV. kerület", "Budapest V. kerület", "Budapest VII. kerület", "Budapest VIII. kerület", "Budapest IX. kerület", "Budapest X. kerület", "Budapest XI. kerület", "Budapest XII. kerület", "Budapest XIII. kerület", "Budapest XIV. kerület", "Budapest XV. kerület", "Budapest XVI. kerület", "Budapest XVII. kerület", "Budapest XVIII. kerület", "Budapest XIX. kerület", "Budapest XX. kerület", "Budapest XXI. kerület", "Budapest XXII. kerület", "Budapest XXIII. kerület", "Budapest"
      ];

      $_POST['states'] = array_merge($_POST['states'], $budapestAreas);

   }

}


$categories = $_POST['categories'] ?: NULL;
$counties = $_POST['counties'] ?: NULL;
$cities = $_POST['states'] ?: NULL;
$name = $_POST['name'] ?: NULL;
$email = $_POST['email'] ?: NULL;
$telephone = $_POST['telephone'] ?: NULL;
$birthday_over = $_POST['birthday_over'] ?: NULL;
$birthday_down = $_POST['birthday_down'] ?: NULL;
$traveling = $_POST['traveling'] ?: NULL;
$foreigner = $_POST['foreigner'] ?: NULL;
$drivings = $_POST['driving_license'] ?: NULL;
$languages = $_POST['languages'] ?: NULL;
$sources = $_POST['sources'] ?: NULL;
$status = $_POST['status'] ?: NULL;
$language_level = $_POST['language_level'] ?: NULL;
$cv = $_POST['cv'] ?: NULL;

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



$csvs = getCvs($con, $cities, $categories, $counties, $name, $email, $telephone , $birthday_over , $birthday_down, $traveling, $foreigner, $languages, $drivings, $sources, $cv, $status);
$data = [];
if($_GET['m'] === "q") {
header('Content-Type: application/json');
   foreach($csvs as $cv) {
      $deleteText = '<br><a href="delete.php?e='.$cv['id'].'" onclick="return confirm(\'Biztos törölni szeretné '.$cv['id'].' - '.$cv['name'].' cv-t?\');">Törlés</a>';
      $selectText = '<select  onchange="statusChange(this, '.$cv['id'].')" id="change-status'.$cv['id'].'" data-id="'.$cv['id'].'" >';
      foreach($statusArray as $key => $status) {
         $selected = ($cv['status'] == $key ) ? 'selected' : '';
         $selectText = $selectText.'<option value="'.$key.'" '.$selected.'>'.$status.'</option>';
      }
      $selectText = $selectText."</select>"; 
      
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
         "settings" => '<a href="edit.php?e='.$cv['id'].'" target="_blank" title="Szerkesztés"><i class="fa fa-edit"></i></a></a>',
      ];

   }

   $return_data = json_encode([
      "records" => $data,
      'count' => count($data)
   ]);
   echo $return_data;

} else {
$filename = date('Ymdhis'). '_csv_export.xlsx';
if(count($csvs) > 0) {
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
   $objPHPExcel = new PHPExcel();
   $activeSheet = $objPHPExcel->getActiveSheet();


   //output headers
   $activeSheet->SetCellValue('A1', 'Felhasználó id');
   $activeSheet->SetCellValue('B1', 'Név');
   $activeSheet->SetCellValue('C1', 'Születési dátum');
   $activeSheet->SetCellValue('D1', 'Email');
   $activeSheet->SetCellValue('E1', 'Telefonszám');
   $activeSheet->SetCellValue('F1', 'Megye');
   $activeSheet->SetCellValue('G1', 'Város');
   $activeSheet->SetCellValue('H1', 'Pozíciók');
   $activeSheet->SetCellValue('I1', 'Forrás');
   $activeSheet->SetCellValue('J1', 'Vezetői engedélyek');
   $activeSheet->SetCellValue('K1', 'Nyelvek');
   $activeSheet->SetCellValue('L1', 'Utazna-e');
   $activeSheet->SetCellValue('M1', 'Km');
   $activeSheet->SetCellValue('N1', 'Külföld');
   $activeSheet->SetCellValue('O1', 'Külföldre hová');
   $activeSheet->SetCellValue('P1', 'CV url');
   $activeSheet->SetCellValue('Q1', 'Létrehozás dátuma');
   $activeSheet->SetCellValue('R1', 'Módosítás dátuma');
   $activeSheet->SetCellValue('S1', 'Megjegyzés');

   //output valuess
   $current_cell = 2;
   foreach ($data as $csv) {
      $objPHPExcel->getActiveSheet()->fromArray($csv, null, 'A'.$current_cell);
      $current_cell++;
   }
   $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
   $objWriter->save('csv/'.$filename);
}

$count = count($csvs);

$link = (count($csvs) > 0) ? "https://mumi.hu/employees/csv/".$filename : '';
header('Content-Type: application/json');
$return_data = json_encode([
"link" => $link,
'count' => $count
]);

echo $return_data;

}


function getCvs($con, $cities, $categories, $counties, $name, $email, $telephone , $birthday_over , $birthday_down, $traveling, $foreigner, $languages, $drivings, $sources, $cv, $status) {


     $sql="
        SELECT * FROM query_employees
      WHERE offer_id IS NULL";

   if($status !== null) {
         $sql = $sql." AND status=".$status." ";
   }

   if($name !== null) {
         $sql = $sql." AND name LIKE '%".$name."%' ";
   }

   if($email !== null) {
         $sql = $sql." AND email LIKE '%".$email."%' ";
   }

   if($telephone !== null) {
         $sql = $sql." AND telephone LIKE '%".$telephone."%' ";
   }

   if($birthday_over !== null) {
         $sql = $sql." AND birthday > '".$birthday_over."' ";
   }

   if($birthday_down !== null) {
         $sql = $sql." AND birthday < '".$birthday_down."' ";
   }

   if($traveling !== null) {
         $value = ($traveling == 1) ? 1 : 0;
         $sql = $sql." AND traveling = ".$value." ";
   }

   if($foreigner !== null) {
         $value = ($foreigner == 1) ? 1 : 0;
         $sql = $sql." AND foreigner = ".$value." ";
   }

   if($sources !== null) {
         $sql = $sql." AND source = '".$sources."' ";
   }

   if($cv !== null) {
     
      if($cv == 1) {
         $sql = $sql." AND cv_url != '' ";
      } else {
         $sql = $sql." AND cv_url = '' ";
      }
   }

   if(is_array($drivings) && count($drivings) > 0) {
      $sql = $sql." AND (";
      foreach($drivings as $key => $driving) {
         if (end(array_keys($drivings)) !== $key) {
            $sql = $sql." driving_license LIKE '%".$driving."%' OR";
         } else {
            $sql = $sql." driving_license LIKE '%".$driving."%') ";
         }
      }
   }

   if(is_array($languages) && count($languages) > 0) {
      $sql = $sql." AND (";
      foreach($languages as $key => $language) {
         if (end(array_keys($languages)) !== $key) {
            $sql = $sql." language LIKE '%".$language."%' OR";
         } else {
            $sql = $sql." language LIKE '%".$language."%') ";
         }
      }
   }

   if(is_array($categories) && count($categories) > 0) {
      $sql = $sql." AND (";
      foreach($categories as $key => $category) {
         if (end(array_keys($categories)) !== $key) {
            $sql = $sql." positions LIKE '%".$category."%' OR";
         } else {
            $sql = $sql." positions LIKE '%".$category."%') ";
         }
      }
   }

   if(is_array($cities) && count($cities) > 0) {
      $sql = $sql." AND (";
      foreach($cities as $key => $city) {
         if (end(array_keys($cities)) !== $key) {
            $sql = $sql." city LIKE '%".$city."%' OR";
         } else {
            $sql = $sql." city LIKE '%".$city."%') ";
         }
      }
   }

   if(is_array($counties) && count($counties) > 0) {
      $sql = $sql." AND (";
      foreach($counties as $key => $county) {
         if (end(array_keys($counties)) !== $key) {
            $sql = $sql." county LIKE '%".$county."%' OR";
         } else {
            $sql = $sql." county LIKE '%".$county."%') ";
         }
      }
    }
    //var_dump($sql);
    //exit;
    $sql= $sql." ORDER BY id DESC";

    $stmt = $con->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;

}

