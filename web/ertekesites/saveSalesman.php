<?php

header('Content-Type: application/json; charset=utf-8');
ini_set('date.timezone', 'Europe/Budapest');
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
require $_SERVER["DOCUMENT_ROOT"].'/ertekesites/PHPMailer/src/Exception.php';
require $_SERVER["DOCUMENT_ROOT"].'/ertekesites/PHPMailer/src/PHPMailer.php';
require $_SERVER["DOCUMENT_ROOT"].'/ertekesites/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if(!$_POST['campaign_id']) {
   $return_data = json_encode([
   "Hiba lépett fel küldés során."
   ]);
   echo $return_data;
   return;
}

// Naplófájl beállítása
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/recaptcha_log.txt';
$timestamp = date('Y-m-d H:i:s');

// reCAPTCHA ellenőrzés
if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
    $return_data = json_encode([
        "A biztonsági ellenőrzés nem sikerült. Kérjük, próbálja újra."
    ]);
    echo $return_data;
    return;
}

$recaptchaToken = $_POST['g-recaptcha-response'];
$projectId = "static-reach-454622-a0"; 
$siteKey = "6LeF2P0qAAAAAAgR-JnzqscuAfdVH8PFF_6hP5_V"; 
$apiKey = "AIzaSyAao6gJuENASXee47Y_pK9fHR32iug1pu8"; 

// reCAPTCHA Enterprise API hívás API kulccsal
$url = "https://recaptchaenterprise.googleapis.com/v1/projects/" . $projectId . "/assessments?key=" . $apiKey;

$data = json_encode([
    "event" => [
        "token" => $recaptchaToken,
        "siteKey" => $siteKey,
        "expectedAction" => "submit"
    ]
]);

// cURL használata a reCAPTCHA ellenőrzéséhez
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Naplózzuk a válaszokat
$log = "[$timestamp] reCAPTCHA Enterprise API kérés (értékesítő):\n";
$log .= "URL: " . $url . "\n";
$log .= "Data: " . $data . "\n";
$log .= "HTTP kód: " . $httpCode . "\n";
$log .= "Válasz: " . $response . "\n\n";
//file_put_contents($logFile, $log, FILE_APPEND);

// Hiba ellenőrzés és kezelés
if ($httpCode != 200) {
    $errorLog = "[$timestamp] HIBA: Az API hívás nem sikerült\n";
    $errorLog .= "HTTP kód: " . $httpCode . "\n";
    $errorLog .= "Hibaüzenet: " . $response . "\n\n";
    file_put_contents($logFile, $errorLog, FILE_APPEND);
    
    // Egyéb API hibák esetén
    $return_data = json_encode([
        "A biztonsági ellenőrzés sikertelen volt. Kérjük, frissítse az oldalt és próbálja újra."
    ]);
    echo $return_data;
    return;
}

// Sikeres API hívás esetén ellenőrizzük a választ
$result = json_decode($response);

// Ellenőrizzük, hogy a token validálás sikeres volt-e
if (!isset($result->tokenProperties) || !$result->tokenProperties->valid) {
    $errorLog = "[$timestamp] HIBA: Érvénytelen reCAPTCHA token\n\n";
    file_put_contents($logFile, $errorLog, FILE_APPEND);
    
    $return_data = json_encode([
        "A biztonsági ellenőrzés nem sikerült. Kérjük, próbálja újra."
    ]);
    echo $return_data;
    return;
}

// Ellenőrizzük a pontszámot, ha elérhető
if (isset($result->riskAnalysis) && isset($result->riskAnalysis->score) && $result->riskAnalysis->score < 0.3) {
    $errorLog = "[$timestamp] HIBA: Túl alacsony pontszám: " . $result->riskAnalysis->score . "\n\n";
    file_put_contents($logFile, $errorLog, FILE_APPEND);
    
    $return_data = json_encode([
        "A biztonsági ellenőrzés nem sikerült. Kérjük, próbálja újra."
    ]);
    echo $return_data;
    return;
}

// Sikeres ellenőrzés naplózása
$successLog = "[$timestamp] SIKERES reCAPTCHA ellenőrzés (értékesítő)";
if (isset($result->riskAnalysis) && isset($result->riskAnalysis->score)) {
    $successLog .= " Score: " . $result->riskAnalysis->score;
}
$successLog .= "\n\n";
//file_put_contents($logFile, $successLog, FILE_APPEND);

$admin_mail = [
   //'karrier@mumi.hu' => 'Karrier - mumi.hu',
   //'andras.lamos@gmail.com' => 'Lamos András',
   'ablonczy.daniel@mumi.hu' => 'Ablonczy Dániel',
   'halasi.beatrix@mumi.hu' => 'Halasi Beatrix',
   'nemes.gyula@mumi.hu' => 'Nemes Gyula',
];

$data = [];

$data['status'] = 1;
$data['name'] = $_POST['name'];
$data['firm_name'] = $_POST['firm_name'];
$data['email'] = $_POST['email'];
$data['telephone'] = $_POST['telephone'];
$data['positions'] = (is_array($_POST['categories'])) ? implode(",", $_POST['categories']) : $_POST['categories'];
$data['campaign_id'] = $_POST['campaign_id'] ?? NULL;
$data['created_at'] = date('Y-m-d H:i:s');
$data['updated_at'] = date('Y-m-d H:i:s');


$con = connectDB('localhost', 'c1_web', 'c1_web', '5DpzFiY@5');
$findCampaign = getCampaign($con, $data['campaign_id']);

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

/*
$existTelephone = findTelephone($con, $data['telephone'], $data['offer_id']);

if(isset($existTelephone['telephone'])) {
   $return_data = json_encode([
   "Már regisztráltak ezzel az telefonszámmal a hírdetésre."
   ]);

   echo $return_data;
   return;
}
*/
/*
if(!empty($data['email'])) {
   $exist = findEmail($con, $data['email'], $data['offer_id']);

   if(isset($exist['email'])) {
      $return_data = json_encode([
      "Már regisztráltak ezzel az e-mail címmel a hírdetésre."
      ]);

      echo $return_data;
      return;
   }

}
*/
/*
$data['cv_url'] = NULL;

if($_FILES['cv_file']['name'] != ''){

   if($_FILES['cv_file']['size'] > 5242880) {
       $return_data = json_encode([
      "A fájl mérete nem lehet több mint 5 Mb-t."
      ]);

      echo $return_data;
      return;
   }
    $test = explode('.', $_FILES['cv_file']['name']);
    $extension = end($test);    

    if(!in_array($extension, ['jpeg', 'jpg', 'png', 'pdf', 'doc', 'docx'])) {
      $return_data = json_encode([
      "A fájl kiterjesztése nem megfelelő."
      ]);

      echo $return_data;
      return;
    }
    $name = md5($_FILES['cv_file']['name'].date('Ymdhis')).'.'.$extension;
    $directory = $_SERVER['DOCUMENT_ROOT'].'/cv_uploads/'.date('Y-m-d');

    if(!is_dir($directory)){
       mkdir($directory, 0755, true);
    }

   $location = $_SERVER['DOCUMENT_ROOT'].'/cv_uploads/'.date('Y-m-d').'/'.$name;

   move_uploaded_file($_FILES['cv_file']['tmp_name'], $location);

   $data['cv_url'] = "https://mumi.hu/cv_uploads/".date('Y-m-d')."/".$name;
   
}
*/
$mailData = $data;
$data['user_id'] = $findCampaign['user_id'];


$save = saveSalesman($con, $data);

$findUser = getUser($con, $findCampaign['user_id']);
$mailData['user_full_name'] = $findUser['full_name'];
$mailData['user_telephone'] = $findUser['telephone'];
$mailData['user_email'] = $findUser['email'];
$admin_mail[$findUser['email'] ] = $findUser['full_name'];



try {
   $mail = new PHPMailer(true);
   //Server settings
   $mail->CharSet="UTF-8";
   $mail->isSMTP();                                            //Send using SMTP
   $mail->Host       = 'smtp-relay.gmail.com';                     //Set the SMTP server to send through
   $mail->SMTPAuth   = false;                                   //Enable SMTP authentication
   $mail->Port       = 25;      
    //Recipients
    $mail->setFrom($findUser['email'], $findUser['full_name']);
    $mail->addAddress($data['email'], $data['name']);     //Add a recipient
    $mail->addReplyTo($findUser['email'], $findUser['full_name']);

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Kapcsolatfelvételi kérelem visszaigazolása  - mumi.hu';

    $body = file_get_contents('mail.html');
    foreach($mailData as $key => $value) {
      $body = str_replace("*".$key."*", $value, $body);
    } 

    $mail->Body    = $body;
    $mail->AltBody = strip_tags($body);

    $mail->send();
} catch (Exception $e) {
   // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}


foreach($admin_mail as $email => $name) {
  
   try {
       $mail = new PHPMailer(true);
         //Server settings
         $mail->CharSet="UTF-8";
         $mail->isSMTP();                                            //Send using SMTP
         $mail->Host       = 'smtp-relay.gmail.com';                     //Set the SMTP server to send through
         $mail->SMTPAuth   = false;                                   //Enable SMTP authentication
         $mail->Port       = 25;      
       //Recipients
       $mail->setFrom('ertekesites@mumi.hu', 'Értékesítés - mumi.hu');
       $mail->addAddress($email, $name);     //Add a recipient
       $mail->addReplyTo('ertekesites@mumi.hu', 'Értékesítés - mumi.hu');

       //Content
       $mail->isHTML(true);                                  //Set email format to HTML
       $mail->Subject = ($email == $findUser['email'] ) ? 'Új kapcsolatfelvételi kérelem érkezett - mumi.hu' : 'Új kapcsolatfelvételi kérelem érkezett - '.$findUser['full_name'];

       $body = file_get_contents('admin_mail.html');
       foreach($mailData as $key => $value) {
         $body = str_replace("*".$key."*", $value, $body);
       } 

       $mail->Body    = $body;
       $mail->AltBody = strip_tags($body);

       $mail->send();
   } catch (Exception $e) {
      // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
   }
}
$return_data = json_encode(
"ok"
);

echo $return_data;

function saveSalesman($con, $data) {

   $sql = "INSERT INTO salesman_registrations (status, name, firm_name, email, telephone, positions, campaign_id, created_at, updated_at, user_id) VALUES(:status, :name, :firm_name, :email, :telephone, :positions, :campaign_id, :created_at, :updated_at, :user_id)";

   $stmt = $con->prepare($sql);

   $stmt->execute($data);
    
   return $stmt;

}

function findEmail($con, $email, $offer_id) {
   $sql="SELECT * FROM query_employees WHERE email = '$email' AND offer_id = $offer_id";

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

function getOfferProp($con, $offer_id) {
    $resultBack = "";
    $sql="
        SELECT *
        FROM offer_dictionary_relation
        WHERE offer_id=$offer_id";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($result as $prop) {

        $sql="
        SELECT value
        FROM dictionary
        WHERE id=".$prop['dictionary_id']." AND dictionary_type=9";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $propResult = $stmt->fetch(PDO::FETCH_ASSOC);
        if(isset($propResult['value'])) {
            $resultBack = $propResult['value'];
        }
    }

    return $resultBack;
}

function getFirm($con, $firm_id) {
    $sql="
        SELECT *
        FROM firm
        WHERE id=$firm_id";
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

function getCampaign($con, $id) {
    $sql="
        SELECT c.*
        FROM salesman_campaigns c
        WHERE c.id=$id";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
}
