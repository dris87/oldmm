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


$yesterday = date('Y-m-d',strtotime("-1 days"));

$date = ($_POST['date']) ? $_POST['date'] : $yesterday;

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

