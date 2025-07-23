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


if($_POST['states']) {
   if(in_array('Budapest', $_POST['states'], true)) {
      $budapestAreas=[
         "Budapest VI. kerület", "Budapest I. kerület", "Budapest II. kerület", "Budapest III. kerület", "Budapest IV. kerület", "Budapest V. kerület", "Budapest VII. kerület", "Budapest VIII. kerület", "Budapest IX. kerület", "Budapest X. kerület", "Budapest XI. kerület", "Budapest XII. kerület", "Budapest XIII. kerület", "Budapest XIV. kerület", "Budapest XV. kerület", "Budapest XVI. kerület", "Budapest XVII. kerület", "Budapest XVIII. kerület", "Budapest XIX. kerület", "Budapest XX. kerület", "Budapest XXI. kerület", "Budapest XXII. kerület", "Budapest XXIII. kerület", "Budapest"
      ];

      $_POST['states'] = array_merge($_POST['states'], $budapestAreas);

   }

}


$categories = $_POST['categories'] ?: NULL;
$name = $_POST['name'] ?: NULL;
$email = $_POST['email'] ?: NULL;
$firm_name = $_POST['frim_name'] ?: NULL;
$telephone = $_POST['telephone'] ?: NULL;
$positions = $_POST['categories'] ?: NULL;
$campaign_id = $_POST['campaign'] ?: NULL;
$user_id = $_POST['user_id'] ?: NULL;
$created_at_down = $_POST['created_at_down'] ?: NULL;
$created_at_over = $_POST['created_at_over'] ?: NULL;
$status = $_POST['status'] ?: NULL;



$salesman = getSales($con, $categories, $name, $email, $telephone, $created_at_over, $created_at_down, $user_id, $campaign_id, $status, $_SESSION['rights'], $_SESSION['query_user_id']);
$data = [];
if($_GET['m'] === "q") {
header('Content-Type: application/json');
   foreach($salesman as $sales) {
      $deleteText = '<br><a href="delete.php?e='.$sales['id'].'" onclick="return confirm(\'Biztos törölni szeretné '.$sales['id'].' - '.$sales['name'].' cv-t?\');">Törlés</a>';
      $selectText = '<select  onchange="statusChange(this, '.$sales['id'].')" id="change-status'.$sales['id'].'" data-id="'.$sales['id'].'" >';
      foreach($salesmanStatusArray as $key => $status) {
         $selected = ($sales['status'] == $key ) ? 'selected' : '';
         $selectText = $selectText.'<option value="'.$key.'" '.$selected.'>'.$status.'</option>';
      }
      $selectText = $selectText."</select>"; 
      $user = getUser($con, $sales['user_id']);
      $campaign = getCampaign($con, $sales['campaign_id']);
      
      $data[] = [
         "id" => $sales['id'],
         "status" => $selectText,
         "firm_name" => $sales['firm_name'],
         "name" => $sales['name'],
         "email" => $sales['email'],
         "telephone" => $sales['telephone'],
         "positions" => str_replace(",", ", ", $sales['positions']),
         "user" => $user['full_name'],
         "campaign" => $campaign['name'],
         "created_at" => $sales['created_at'],
         "updated_at" => $sales['updated_at'],
         //"settings" => '<a href="salesman_edit.php?e='.$sales['id'].'" target="_blank" title="Szerkesztés"><i class="fa fa-edit"></i></a></a>',
      ];

   }

   $return_data = json_encode([
      "records" => $data,
      'count' => count($data)
   ]);
   echo $return_data;

} else {
$filename = date('Ymdhis'). '_salesman_export.xlsx';
if(count($salesman) > 0) {
    foreach($salesman as $sales) {
      $campaign = getCampaign($con, $sales['campaign_id']);
      $user = getUser($con, $sales['user_id']);
      $data[] = [
         "id" => $sales['id'],
         'status' => $sales['status'],
         "firm_name" => $sales['firm_name'],
         "name" => $sales['name'],
         "email" => $sales['email'],
         "telephone" => $sales['telephone'],
         "positions" => $sales['positions'],
         "campaign" => $campaign['name'],
         "user" => $user['full_name'],
         "created_at" => $sales['created_at'],
         "updated_at" => $sales['updated_at'],
         "comment" => $sales['comment'], 
      ];

   }
   $objPHPExcel = new PHPExcel();
   $activeSheet = $objPHPExcel->getActiveSheet();


   //output headers
   $activeSheet->SetCellValue('A1', 'Érdeklődő id');
   $activeSheet->SetCellValue('B1', 'Státusz');
   $activeSheet->SetCellValue('C1', 'Cég név');
   $activeSheet->SetCellValue('D1', 'Név');
   $activeSheet->SetCellValue('E1', 'Email cím');
   $activeSheet->SetCellValue('F1', 'Telefonszám');
   $activeSheet->SetCellValue('G1', 'Pozíciók');
   $activeSheet->SetCellValue('H1', 'Kampány');
   $activeSheet->SetCellValue('I1', 'Értékesítő');
   $activeSheet->SetCellValue('J1', 'Létrehozás dátuma');
   $activeSheet->SetCellValue('K1', 'Módosítás dátuma');
   $activeSheet->SetCellValue('L1', 'Megjegyzés');
   /*$activeSheet->SetCellValue('M1', 'Km');
   $activeSheet->SetCellValue('N1', 'Külföld');
   $activeSheet->SetCellValue('O1', 'Külföldre hová');
   $activeSheet->SetCellValue('P1', 'CV url');
   $activeSheet->SetCellValue('Q1', 'Létrehozás dátuma');
   $activeSheet->SetCellValue('R1', 'Módosítás dátuma');
   $activeSheet->SetCellValue('S1', 'Megjegyzés');
   $activeSheet->SetCellValue('T1', 'Hírdetés');
   $activeSheet->SetCellValue('U1', 'Hírdetés url');*/

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


function getSales($con, $categories, $name, $email, $telephone, $created_at_over, $created_at_down, $user_id, $campaign_id, $status, $admin, $admin_user_id) {


     $sql="
        SELECT * FROM salesman_registrations
      WHERE user_id IS NOT NULL";

   if($admin != '1') {
      $sql = $sql." AND user_id=".$admin_user_id." ";
   }

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

   if($created_at_over !== null) {
         $sql = $sql." AND created_at > '".$created_at_over."' ";
   }

   if($created_at_down !== null) {
         $sql = $sql." AND created_at < '".$created_at_down."' ";
   }


   if($user_id !== null) {
         $sql = $sql." AND user_id IN (".implode(", ", $user_id).") ";
   }


   if(is_array($campaign_id) && count($campaign_id) > 0) {
         $sql = $sql." AND campaign_id IN (".implode(", ", $campaign_id).") ";
   }

    //var_dump($sql);
    //exit;
    $sql= $sql." ORDER BY id DESC";

    $stmt = $con->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;

}

function getOffer($con, $offer_id) {
    $sql="
        SELECT *
        FROM offer
        WHERE id=$offer_id";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
}

function getUser($con, $user_id) {
    $sql="
        SELECT *
        FROM query_users
        WHERE id=$user_id";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
}

function getCampaign($con, $campaign_id) {
    if($campaign_id !== NULL) {
    $sql="
        SELECT *
        FROM salesman_campaigns
        WHERE id=$campaign_id";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;

   }

   return "";
}

