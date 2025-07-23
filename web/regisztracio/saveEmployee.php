<?php
session_start(); // Session indítása a CSRF token eléréséhez

header('Content-Type: application/json; charset=utf-8');
ini_set('date.timezone', 'Europe/Budapest');
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

require $_SERVER["DOCUMENT_ROOT"].'/jelentkezes/PHPMailer/src/Exception.php';
require $_SERVER["DOCUMENT_ROOT"].'/jelentkezes/PHPMailer/src/PHPMailer.php';
require $_SERVER["DOCUMENT_ROOT"].'/jelentkezes/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// IP blokkolási konfiguráció
$maxSecurityViolations = 5 // Maximum biztonsági hibák száma
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

// Biztonsági esemény rögzítése és IP blokkolás kezelése
function recordSecurityViolation($ip, $violationType, $details, $maxViolations, $blockDurationHours) {
    $con = connectDB('localhost', 'c1_web', 'c1_web', '5DpzFiY@5');
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown User-Agent';
    $postData = json_encode($_POST);
    
    // Biztonsági tábla létrehozása, ha nem létezik
    $createTableSql = "CREATE TABLE IF NOT EXISTS security_violations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        user_agent VARCHAR(500),
        timestamp DATETIME NOT NULL,
        violation_type VARCHAR(50) NOT NULL,
        details TEXT,
        post_data TEXT,
        blocked_until DATETIME NULL,
        attempt_count INT DEFAULT 1,
        INDEX idx_ip_timestamp (ip_address, timestamp),
        INDEX idx_violation_type (violation_type),
        INDEX idx_blocked_until (blocked_until)
    )";
    $con->exec($createTableSql);
    
    // Új biztonsági esemény rögzítése
    $sql = "INSERT INTO security_violations 
            (ip_address, user_agent, timestamp, violation_type, details, post_data, attempt_count) 
            VALUES (:ip, :user_agent, NOW(), :violation_type, :details, :post_data, 1)";
    
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':ip', $ip);
    $stmt->bindParam(':user_agent', $userAgent);
    $stmt->bindParam(':violation_type', $violationType);
    $stmt->bindParam(':details', $details);
    $stmt->bindParam(':post_data', $postData);
    $stmt->execute();
    
    // Utolsó 1 órában hány biztonsági hiba volt ettől az IP-től
    $countSql = "SELECT COUNT(*) as violation_count 
                 FROM security_violations 
                 WHERE ip_address = :ip 
                 AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
                 
    $countStmt = $con->prepare($countSql);
    $countStmt->bindParam(':ip', $ip);
    $countStmt->execute();
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    
    $violationCount = $countResult['violation_count'];
    
    // Ha elérte a maximális hibák számát, blokkolja
    if ($violationCount >= $maxViolations) {
        $blockedUntil = date('Y-m-d H:i:s', time() + ($blockDurationHours * 3600));
        
        // Az aktuális esemény frissítése blokkolási adatokkal
        $updateSql = "UPDATE security_violations 
                      SET blocked_until = :blocked_until, attempt_count = :attempt_count
                      WHERE id = LAST_INSERT_ID()";
                      
        $updateStmt = $con->prepare($updateSql);
        $updateStmt->bindParam(':blocked_until', $blockedUntil);
        $updateStmt->bindParam(':attempt_count', $violationCount);
        $updateStmt->execute();
        
        return true; // IP blokkolva
    }
    
    return false; // Még nincs blokkolva
}

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
    // CSRF hiba rögzítése csak akkor, ha nem whitelist-en van
    if (!in_array($currentIP, $whitelistedIPs)) {
        $isBlocked = recordSecurityViolation(
            $currentIP, 
            'csrf_failure', 
            'Invalid or missing CSRF token', 
            $maxSecurityViolations, 
            $blockDurationHours
        );
        
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
    
    echo json_encode("Biztonsági hiba történt. Kérjük, frissítse az oldalt és próbálja újra.");
    exit;
}

// Honeypot ellenőrzés
if (!empty($_POST['company_website'])) {
    // Bot észlelve - rögzítjük és esetleg blokkolja az IP-t
    if (!in_array($currentIP, $whitelistedIPs)) {
        $isBlocked = recordSecurityViolation(
            $currentIP, 
            'bot_detected', 
            $_POST['company_website'], 
            $maxSecurityViolations, 
            $blockDurationHours
        );
        
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
    echo json_encode("ok");
    exit;
}
class RegistrationHandler {
   private $con;
   private $data;
   private $ip_address;
   private $admin_mail = [
       'ugyfelszolgalat@mumi.hu' => 'Ügyfélszolgálat - mumi.hu'
   ];
   
   public function __construct($connection, $formData) {
       $this->con = $connection;
       $this->data = $this->prepareData($formData);
       $this->ip_address = getRealIPAddress();
   }
   
   private function prepareData($post) {
       return [
           'status' => 9,
           'name' => $post['name'],
           'email' => $post['email'],
           'birthday' => $post['birthday'] ?: NULL,
           'telephone' => $post['telephone'],
           'county' => is_array($post['counties']) ? implode(",", $post['counties']) : $post['counties'],
           'city' => is_array($post['states']) ? implode(",", $post['states']) : $post['states'],
           'positions' => is_array($post['categories']) ? implode(",", $post['categories']) : $post['categories'],
           'driving_license' => is_array($post['driving_license']) ? implode(",", $post['driving_license']) : $post['driving_license'],
           'languages' => is_array($post['languages']) ? implode(",", $post['languages']) : $post['languages'],
           'traveling' => $post['traveling'] ?: 0,
           'traveling_km' => $post['traveling_km'] ?: 0,
           'foreigner' => (int) $post['foreign'] ?: 0,
           'where_foreign' => $post['where_foreign'],
           'cv_url' => $post['cv_url'],
           'offer_id' => $post['offer_id'] ?? NULL,
           'created_at' => date('Y-m-d H:i:s'),
           'updated_at' => date('Y-m-d H:i:s')
       ];
   }

   public function checkBlockedRegistration() {
        // Először ellenőrizzük a query_employees táblában
        $sql = "SELECT * FROM query_employees WHERE (email = :email OR email LIKE :pattern) AND offer_id IS NULL";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([
            'email' => $this->data['email'],
            'pattern' => '%CHECK MONEY%'
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return true; // Ha találtunk egyezést, megakadályozzuk a regisztrációt
        }

        // Ezután ellenőrizzük a blocked_registrations táblában
        $sql = "SELECT * FROM blocked_registrations WHERE email = :email OR telephone = :telephone";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([
            'email' => $this->data['email'],
            'telephone' => $this->data['telephone']
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Ha találtunk egyezést a blocked_registrations táblában, 
            // csak az IP címet mentjük el
            $this->saveBlockedIP($this->ip_address);
            return true;
        }
        
        return false;
    }

    private function saveBlockedIP($ip_address) {
        $sql = "INSERT INTO blocked_registrations (ip_address) VALUES (:ip_address)";
        $stmt = $this->con->prepare($sql);
        return $stmt->execute(['ip_address' => $ip_address]);
    }
    public function handleRegistration() {
        try {
            // Naplófájl beállítása
            $logFile = $_SERVER['DOCUMENT_ROOT'] . '/recaptcha_log.txt';
            $timestamp = date('Y-m-d H:i:s');
            
            // reCAPTCHA ellenőrzés
            if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
                return json_encode("A biztonsági ellenőrzés nem sikerült. Kérjük, próbálja újra.");
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
            if (isset($logFile)) {
                $log = "[$timestamp] reCAPTCHA Enterprise API kérés (regisztráció):\n";
                $log .= "URL: " . $url . "\n";
                $log .= "Data: " . $data . "\n";
                $log .= "HTTP kód: " . $httpCode . "\n";
                $log .= "Válasz: " . $response . "\n\n";
                //file_put_contents($logFile, $log, FILE_APPEND);
            }

            // Hiba ellenőrzés és kezelés
            if ($httpCode != 200) {
                if (isset($logFile)) {
                    $errorLog = "[$timestamp] HIBA: Az API hívás nem sikerült\n";
                    $errorLog .= "HTTP kód: " . $httpCode . "\n";
                    $errorLog .= "Hibaüzenet: " . $response . "\n\n";
                    file_put_contents($logFile, $errorLog, FILE_APPEND);
                }
                
                return json_encode("A biztonsági ellenőrzés sikertelen volt. Kérjük, frissítse az oldalt és próbálja újra.");
            }

            // Sikeres API hívás esetén ellenőrizzük a választ
            $result = json_decode($response);

            // Ellenőrizzük, hogy a token validálás sikeres volt-e
            if (!isset($result->tokenProperties) || !$result->tokenProperties->valid) {
                if (isset($logFile)) {
                    $errorLog = "[$timestamp] HIBA: Érvénytelen reCAPTCHA token\n\n";
                    file_put_contents($logFile, $errorLog, FILE_APPEND);
                }
                
                return json_encode("A biztonsági ellenőrzés nem sikerült. Kérjük, próbálja újra.");
            }

            // Ellenőrizzük a pontszámot, ha elérhető
            if (isset($result->riskAnalysis) && isset($result->riskAnalysis->score) && $result->riskAnalysis->score < 0.3) {
                if (isset($logFile)) {
                    $errorLog = "[$timestamp] HIBA: Túl alacsony pontszám: " . $result->riskAnalysis->score . "\n\n";
                    file_put_contents($logFile, $errorLog, FILE_APPEND);
                }
                
                return json_encode("A biztonsági ellenőrzés nem sikerült. Kérjük, próbálja újra.");
            }

            // Sikeres ellenőrzés naplózása
            if (isset($logFile)) {
                $successLog = "[$timestamp] SIKERES reCAPTCHA ellenőrzés (regisztráció)";
                if (isset($result->riskAnalysis) && isset($result->riskAnalysis->score)) {
                    $successLog .= " Score: " . $result->riskAnalysis->score;
                }
                $successLog .= "\n\n";
                //file_put_contents($logFile, $successLog, FILE_APPEND);
            }
                
            // A többi meglévő kód...
            if ($this->checkBlockedRegistration()) {
                return json_encode("Ez az email cím vagy telefonszám nem engedélyezett.");
            }

            // CV fájl kezelése
            if ($_FILES['cv_file']['name'] != '') {
                $cvResult = $this->handleCVUpload();
                if (isset($cvResult['error'])) {
                    return json_encode($cvResult['error']);
                }
                $this->data['cv_url'] = $cvResult['cv_url'];
            }

            // Mentsük az adatokat
            if ($this->saveRegistration()) {
                // Emailek küldése
                $this->sendEmails();
                
                // CSRF token frissítése sikeres regisztráció után
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
                return json_encode("ok");
            }

            throw new Exception('Hiba történt a mentés során.');

        } catch (Exception $e) {
            return json_encode($e->getMessage());
        }
    }

    private function handleCVUpload() {
       if ($_FILES['cv_file']['size'] > 5242880) {
           return ['error' => 'A fájl mérete nem lehet több mint 5 Mb-t.'];
       }

       $extension = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
       if (!in_array($extension, ['jpeg', 'jpg', 'png', 'pdf', 'doc', 'docx'])) {
           return ['error' => 'A fájl kiterjesztése nem megfelelő.'];
       }

       $fileName = md5($_FILES['cv_file']['name'] . date('Ymdhis')) . '.' . $extension;
       $directory = $_SERVER['DOCUMENT_ROOT'] . '/cv_uploads/' . date('Y-m-d');
       
       if (!is_dir($directory)) {
           mkdir($directory, 0755, true);
       }

       $location = $directory . '/' . $fileName;
       if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $location)) {
           return ['cv_url' => "https://mumi.hu/cv_uploads/" . date('Y-m-d') . "/" . $fileName];
       }

       return ['error' => 'Hiba történt a fájl feltöltése során.'];
   }

   private function saveRegistration() {
       $sql = "INSERT INTO query_employees (
           status, name, email, birthday, telephone, county, city, 
           positions, traveling, traveling_km, foreigner, where_foreign, 
           cv_url, created_at, updated_at, source, language, driving_license
       ) VALUES (
           :status, :name, :email, :birthday, :telephone, :county, :city,
           :positions, :traveling, :traveling_km, :foreigner, :where_foreign,
           :cv_url, :created_at, :updated_at, :source, :languages, :driving_license
       )";

       $params = [
           ':status' => $this->data['status'],
           ':name' => $this->data['name'],
           ':email' => $this->data['email'],
           ':birthday' => $this->data['birthday'],
           ':telephone' => $this->data['telephone'],
           ':county' => $this->data['county'],
           ':city' => $this->data['city'],
           ':positions' => $this->data['positions'],
           ':traveling' => $this->data['traveling'],
           ':traveling_km' => $this->data['traveling_km'],
           ':foreigner' => $this->data['foreigner'],
           ':where_foreign' => $this->data['where_foreign'],
           ':cv_url' => $this->data['cv_url'],
           ':created_at' => $this->data['created_at'],
           ':updated_at' => $this->data['updated_at'],
           ':source' => 'mumi',
           ':languages' => $this->data['languages'],
           ':driving_license' => $this->data['driving_license']
       ];

       $stmt = $this->con->prepare($sql);
       return $stmt->execute($params);
   }

   private function sendEmails() {
       // User email
       $this->sendEmail(
           $this->data['email'],
           $this->data['name'],
           'Regisztráció visszaigazolása - mumi.hu',
           'mail.html'
       );

       // Admin emails
       foreach ($this->admin_mail as $email => $name) {
            $this->sendEmail(
               $email,
               $name,
               'Új regisztráció érkezett - mumi.hu',
               'admin_mail.html',
               true
           );
       }
   }
   private function sendEmail($to, $toName, $subject, $template, $isAdmin = false) {
       try {
           $mail = new PHPMailer(true);
           $mail->CharSet = "UTF-8";
           $mail->isSMTP();
           $mail->Host = 'smtp-relay.gmail.com';
           $mail->SMTPAuth = false;
           $mail->Port = 25;
           
           $mail->setFrom('ugyfelszolgalat@mumi.hu', 'Ügyfélszolgálat - mumi.hu');
           $mail->addAddress($to, $toName);
           $mail->addReplyTo('ugyfelszolgalat@mumi.hu', 'Ügyfélszolgálat - mumi.hu');

           $mail->isHTML(true);
           $mail->Subject = $subject;

           $mailData = $this->data;
           if ($isAdmin) {
               $mailData['traveling'] = $mailData['traveling'] ? "Igen" : "Nem";
               $mailData['foreigner'] = $mailData['foreigner'] ? "Igen" : "Nem";
               $mailData['telephone'] = htmlspecialchars($mailData['telephone']);
           }

           $body = file_get_contents($template);
           foreach ($mailData as $key => $value) {
               $body = str_replace("*".$key."*", $value, $body);
           }

           $mail->Body = $body;
           $mail->AltBody = strip_tags($body);

           $mail->send();
           return true;
       } catch (Exception $e) {
           return false;
       }
   }
}

// Használat
try {
   $con = new PDO('mysql:host=localhost;dbname=c1_web', 'c1_web', '5DpzFiY@5');
   $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   $con->exec("SET CHARACTER SET utf8");
   $con->exec("SET NAMES utf8");

   $handler = new RegistrationHandler($con, $_POST);
   echo $handler->handleRegistration();
} catch (Exception $e) {
   echo json_encode("Adatbázis kapcsolódási hiba");
}