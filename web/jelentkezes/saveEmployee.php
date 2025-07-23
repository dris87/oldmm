<?php

header('Content-Type: application/json; charset=utf-8');
ini_set('date.timezone', 'Europe/Budapest');
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

session_start();

require $_SERVER["DOCUMENT_ROOT"].'/jelentkezes/PHPMailer/src/Exception.php';
require $_SERVER["DOCUMENT_ROOT"].'/jelentkezes/PHPMailer/src/PHPMailer.php';
require $_SERVER["DOCUMENT_ROOT"].'/jelentkezes/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// IP blokkolási konfiguráció
$maxBotAttempts = 5; // Maximum bot kísérletek száma
$blockDurationHours = 2; // Blokkolás időtartama órában
$whitelistedIPs = [
    '127.0.0.1',
    '::1',
    // Itt add hozzá a saját IP címedet, ha szükséges
    // '192.168.1.100',
];

// Aktuális IP cím megszerzése
function getRealIPAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
        return $_SERVER['HTTP_X_FORWARDED'];
    } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
        return $_SERVER['HTTP_FORWARDED'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// IP blokkolás ellenőrzése adatbázisból
function isIPBlocked($ip) {
    $con = connectDB('localhost', 'c1_web', 'c1_web', '5DpzFiY@5');
    
    $sql = "SELECT blocked_until FROM security_violations 
            WHERE ip_address = :ip AND blocked_until IS NOT NULL 
            ORDER BY timestamp DESC LIMIT 1";
    
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':ip', $ip);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && $result['blocked_until']) {
        $blockTime = strtotime($result['blocked_until']);
        
        // Ha még mindig blokkolva van
        if (time() < $blockTime) {
            return true;
        } else {
            // Blokkolás lejárt, töröljük a blocked_until értéket
            $updateSql = "UPDATE security_violations 
                         SET blocked_until = NULL 
                         WHERE ip_address = :ip";
            $updateStmt = $con->prepare($updateSql);
            $updateStmt->bindParam(':ip', $ip);
            $updateStmt->execute();
            return false;
        }
    }
    
    return false;
}

// Bot kísérlet rögzítése adatbázisba és IP blokkolás kezelése
function recordBotAttempt($ip, $maxAttempts, $blockDurationHours) {
    $con = connectDB('localhost', 'c1_web', 'c1_web', '5DpzFiY@5');
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Ismeretlen User-Agent';
    $honeypotValue = $_POST['company_name'] ?? '';
    $postData = json_encode($_POST);
    
    // Új bot kísérlet rögzítése
    $sql = "INSERT INTO security_violations 
            (ip_address, user_agent, timestamp, violation_type, details, post_data, attempt_count) 
            VALUES (:ip, :user_agent, NOW(), 'honeypot_triggered', :details, :post_data, 1)";
    
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':ip', $ip);
    $stmt->bindParam(':user_agent', $userAgent);
    $stmt->bindParam(':details', $honeypotValue);
    $stmt->bindParam(':post_data', $postData);
    $stmt->execute();
    
    // Utolsó 24 órában hány kísérlet volt ettől az IP-től
    $countSql = "SELECT COUNT(*) as attempt_count 
                 FROM security_violations 
                 WHERE ip_address = :ip 
                 AND violation_type = 'honeypot_triggered' 
                 AND timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                 
    $countStmt = $con->prepare($countSql);
    $countStmt->bindParam(':ip', $ip);
    $countStmt->execute();
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    
    $attemptCount = $countResult['attempt_count'];
    
    // Ha elérte a maximális kísérleteket, blokkolja
    if ($attemptCount >= $maxAttempts) {
        $blockedUntil = date('Y-m-d H:i:s', time() + ($blockDurationHours * 3600));
        
        // Az aktuális kísérlet frissítése blokkolási adatokkal
        $updateSql = "UPDATE security_violations 
                      SET blocked_until = :blocked_until, attempt_count = :attempt_count
                      WHERE id = LAST_INSERT_ID()";
                      
        $updateStmt = $con->prepare($updateSql);
        $updateStmt->bindParam(':blocked_until', $blockedUntil);
        $updateStmt->bindParam(':attempt_count', $attemptCount);
        $updateStmt->execute();
        
        return true; // IP blokkolva
    }
    
    return false; // Még nincs blokkolva
}

$currentIP = getRealIPAddress();

// Whitelist ellenőrzés első körben
if (!in_array($currentIP, $whitelistedIPs)) {
    // Ha már blokkolva van az IP, 404-et ad
    if (isIPBlocked($currentIP)) {
        http_response_code(404);
        echo '<!DOCTYPE html>
<html>
<head>
    <title>404 Not Found</title>
</head>
<body>
    <h1>Not Found</h1>
    <p>The requested URL was not found on this server.</p>
</body>
</html>';
        exit;
    }
}

// CSRF Token ellenőrzés
if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
    $return_data = json_encode([
        "Biztonsági hiba: Érvénytelen kérés. Kérjük, frissítse az oldalt és próbálja újra."
    ]);
    echo $return_data;
    return;
}

// Honeypot ellenőrzés
if (!empty($_POST['company_name'])) {
    // Bot észlelve - rögzítjük és esetleg blokkolja az IP-t
    if (!in_array($currentIP, $whitelistedIPs)) {
        $isBlocked = recordBotAttempt($currentIP, $maxBotAttempts, $blockDurationHours);
        
        if ($isBlocked) {
            // Ha most lett blokkolva, 404-et ad
            http_response_code(404);
            echo '<!DOCTYPE html>
<html>
<head>
    <title>404 Not Found</title>
</head>
<body>
    <h1>Not Found</h1>
    <p>The requested URL was not found on this server.</p>
</body>
</html>';
            exit;
        }
    }
    
    // Hamis "sikeres" válasz küldése, hogy ne áruljuk el a botnak, hogy észleltük
    $return_data = json_encode("ok");
    echo $return_data;
    return;
}

// Kötelező mezők ellenőrzése
$required_fields = [
    'offer_id' => 'Hirdetés azonosító',
    'name' => 'Név',
    'telephone' => 'Telefonszám'
];

$errors = [];

if (!isset($_POST['g-recaptcha-response'])) {
    $return_data = json_encode([
        "A biztonsági ellenőrzés nem sikerült. Kérjük, próbálja újra."
    ]);
    echo $return_data;
    return;
}

/// Naplófájl beállítása
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
$log = "[$timestamp] reCAPTCHA Enterprise API kérés:\n";
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
    
    // Az API szerver hibája esetén (vagy ha a mumi.hu nincs engedélyezve)
    // Ez a blokk csak akkor fut le, ha a referrer korlátozás aktív az API kulcson
    if ($httpCode == 403 && strpos($response, "referer") !== false) {
        $return_data = json_encode([
            "A biztonsági ellenőrzés ideiglenesen nem elérhető. Kérjük, próbálja újra később vagy forduljon az ügyfélszolgálathoz."
        ]);
        echo $return_data;
        return;
    }
    
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
    // Token nem érvényes, blokkolás
    $return_data = json_encode(["A biztonsági ellenőrzés nem sikerült. Kérjük, próbálja újra."]);
    echo $return_data;
    return;
}

// Ellenőrizzük a pontszámot (0.5 alatt blokkolunk, ezt módosíthatod)
if (isset($result->riskAnalysis) && isset($result->riskAnalysis->score) && $result->riskAnalysis->score < 0.5) {
    // Túl alacsony pontszám, blokkolás
    $return_data = json_encode(["A biztonsági ellenőrzés nem sikerült. Kérjük, próbálja újra."]);
    echo $return_data;
    return;
}

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
$successLog = "[$timestamp] SIKERES reCAPTCHA ellenőrzés";
if (isset($result->riskAnalysis) && isset($result->riskAnalysis->score)) {
    $successLog .= " Score: " . $result->riskAnalysis->score;
}
$successLog .= "\n\n";
//file_put_contents($logFile, $successLog, FILE_APPEND);

// Itt folytatódik az eredeti kód a kötelező mezők ellenőrzésével...

foreach ($required_fields as $field => $label) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        $errors[] = "A(z) $label mező kitöltése kötelező!";
    }
}

// Telefonszám formátum ellenőrzése
if (isset($_POST['telephone']) && !empty($_POST['telephone'])) {
    // Csak számokat és + jelet engedünk meg
    if (!preg_match('/^[0-9+\s-]+$/', $_POST['telephone'])) {
        $errors[] = "A telefonszám csak számokat, + jelet, kötőjelet és szóközt tartalmazhat!";
    }
    // Minimum 8 számjegy (mobil vagy vezetékes)
    if (strlen(preg_replace('/[^0-9]/', '', $_POST['telephone'])) < 8) {
        $errors[] = "A telefonszám túl rövid! Minimum 8 számjegy szükséges.";
    }
}

// Név ellenőrzése
if (isset($_POST['name']) && !empty($_POST['name'])) {
    // Minimum 3 karakter, magyar ábécé összes betűje, szóköz, kötőjel és pont
    if (!preg_match('/^[a-záéíóöőúüűàâäçèêëìîïñòôöùûüÿýąćęłńóśźżĄĆĘŁŃÓŚŹŻßÁÉÍÓÖŐÚÜŰA-Z\s\.-]{3,}$/', $_POST['name'])) {
        $errors[] = "A név csak betűket, pontot, kötőjelet és szóközt tartalmazhat, és minimum 3 karakter hosszú kell legyen!";
    }
}

// Email formátum ellenőrzése, ha meg van adva
if (isset($_POST['email']) && !empty($_POST['email'])) {
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Érvénytelen email cím formátum!";
    }
}

// Ha van hiba, visszaküldjük őket
if (!empty($errors)) {
    $return_data = json_encode($errors);
    echo $return_data;
    return;
}

// Az eredeti kód további része változatlan marad
if(!$_POST['offer_id']) {
   $return_data = json_encode([
   "Hiba lépett fel küldés során."
   ]);
   echo $return_data;
   return;
}

$admin_mail = [
   'karrier@mumi.hu' => 'Karrier - mumi.hu',
   //'andras.lamos@gmail.com' => 'teszt',
];

$data = [];

$data['status'] = 1;
$data['name'] = $_POST['name'];
$data['email'] = $_POST['email'];
$data['telephone'] = $_POST['telephone'];
$data['county'] = (is_array($_POST['counties'])) ? implode(",", $_POST['counties']) : $_POST['counties'];
$data['city'] = (is_array($_POST['states'])) ? implode(",", $_POST['states']) : $_POST['states'];
$data['positions'] = (is_array($_POST['categories'])) ? implode(",", $_POST['categories']) : $_POST['categories'];
$data['driving_license'] = (is_array($_POST['driving_license'])) ? implode(",", $_POST['driving_license']) : $_POST['driving_license'];
$data['cv_url'] = $_POST['cv_url'];
$data['offer_id'] = $_POST['offer_id'] ?? NULL;
$data['created_at'] = date('Y-m-d H:i:s');
$data['updated_at'] = date('Y-m-d H:i:s');


$con = connectDB('localhost', 'c1_web', 'c1_web', '5DpzFiY@5');
$findOffer = getOffer($con, $data['offer_id']);
$city = getOfferProp($con, $findOffer['id']);
$firm = getFirm($con, $findOffer['firm_id']);
$data['offer_position'] = $findOffer['title'];
$data['offer_city'] = $city;
$data['offer_url'] = "https://mumi.hu/hu/allas/".$findOffer['slug'];
$data['firm_name'] = $firm['name'];


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

$existTelephone = findTelephone($con, $data['telephone'], $data['offer_id']);

if(isset($existTelephone['telephone'])) {
   $return_data = json_encode([
   "Már regisztráltak ezzel az telefonszámmal a hírdetésre."
   ]);

   echo $return_data;
   return;
}

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
$mailData = $data;

$save = saveCv($con, $data);

try {
   $mail = new PHPMailer(true);
   //Server settings
   $mail->CharSet="UTF-8";
   $mail->isSMTP();                                            //Send using SMTP
   $mail->Host       = 'smtp-relay.gmail.com';                     //Set the SMTP server to send through
   $mail->SMTPAuth   = false;                                   //Enable SMTP authentication
   $mail->Port       = 25;      
    //Recipients
    $mail->setFrom('ugyfelszolgalat@mumi.hu', 'Ügyfélszolgálat - mumi.hu');
    $mail->addAddress($data['email'], $data['name']);     //Add a recipient
    $mail->addReplyTo('ugyfelszolgalat@mumi.hu', 'Ügyfélszolgálat - mumi.hu');

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Jelentkezés visszaigazolása - '.$findOffer['title'].' ( '.$city.' ) - mumi.hu';

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
       $mail->setFrom('ugyfelszolgalat@mumi.hu', 'Ügyfélszolgálat - mumi.hu');
       $mail->addAddress($email, $name);     //Add a recipient
       $mail->addReplyTo('ugyfelszolgalat@mumi.hu', 'Ügyfélszolgálat - mumi.hu');

       $minimal_package_text = ($findOffer['minimal_package'] == '1') ? 'MINIMAL CSOMAG ' : '';
       //Content
       $mail->isHTML(true);                                  //Set email format to HTML
       $mail->Subject = 'Jelentkezés érkezett '.$minimal_package_text.'- '.$firm['name'].', '.$findOffer['title'].' ( '.$city.' ) - mumi.hu';
       $mailData['minimal_package_text'] = ($findOffer['minimal_package'] == '1') ? "Típus: Minimál csomag 
       " : '';
       $mailData['title'] = $findOffer['title'];
       $mailData['offer_city'] = $city;
       $mailData['offer_position'] = $findOffer['title'];
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

if($findOffer['minimal_package'] == '1' && !empty($findOffer['minimal_email'])) {
    // Ha minimal_without_cv = 0: mindig küldjünk e-mailt (CV-vel vagy anélkül)
    // Ha minimal_without_cv = 1: csak akkor küldjünk, ha van CV csatolva
    $shouldSendEmail = false;
    
    if($findOffer['minimal_without_cv'] == '0') {
        // CV nélkül is elfogadja → mindig küldjünk
        $shouldSendEmail = true;
    } elseif($findOffer['minimal_without_cv'] == '1' && !empty($data['cv_url'])) {
        // Csak CV-vel fogadja el ÉS van is CV → küldjünk
        $shouldSendEmail = true;
    }
    
    if($shouldSendEmail) {
        try {
            $title = (!empty($findOffer['minimal_title'])) ? $findOffer['minimal_title'] : $findOffer['title'];
            $mailData['title'] = (!empty($findOffer['title'])) ? $findOffer['title'] : $mailData['title'];
            $city = (!empty($findOffer['minimal_city'])) ? $findOffer['minimal_city'] : $city;
            $mailData['offer_url'] = (!empty($findOffer['minimal_url'])) ? $findOffer['minimal_url'] : $mailData['offer_url'];
            $mailData['offer_city'] = $city;
            $mailData['offer_position'] = $title;
            
           $mail = new PHPMailer(true);
           //Server settings
           $mail->CharSet="UTF-8";
           $mail->isSMTP();
           $mail->Host       = 'smtp-relay.gmail.com';
           $mail->SMTPAuth   = false;
           $mail->Port       = 25;      
            //Recipients
            $mail->setFrom('ugyfelszolgalat@mumi.hu', 'Ügyfélszolgálat - mumi.hu');
            $mail->addAddress($findOffer['minimal_email'], $firm['name']);
            $mail->addReplyTo('ugyfelszolgalat@mumi.hu', 'Ügyfélszolgálat - mumi.hu');

            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Jelentkezés érkezett - '.$title.' ( '.$city.' ) - mumi.hu';

            $body = file_get_contents('firm_mail.html');
            foreach($mailData as $key => $value) {
              $body = str_replace("*".$key."*", $value, $body);
            } 

            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
        } catch (Exception $e) {
           error_log("Minimal package mail error: " . $mail->ErrorInfo);
        }
    }
}

// CSRF token regenerálása a sikeres beküldés után
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$return_data = json_encode(
"ok"
);

echo $return_data;

function saveCv($con, $data) {

   unset($data['offer_position']);
   unset($data['offer_city']);
   unset($data['offer_url']);
   unset($data['firm_name']);

   $data['source'] = "Direkt jelentkező";

   $sql = "INSERT INTO query_employees (status, name, email, telephone, county, city, positions, cv_url, created_at, updated_at, offer_id, driving_license, source) VALUES(:status, :name, :email, :telephone, :county, :city, :positions, :cv_url, :created_at, :updated_at, :offer_id, :driving_license, :source)";

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