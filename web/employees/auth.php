<?php
// Session kezelés ellenőrzéssel
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params('86400');
    ini_set("session.gc_maxlifetime", 86400);
    ini_set("session.cookie_lifetime", 86400);
    session_start([
        'cookie_lifetime' => 86400,
    ]);
}

ini_set('memory_limit', '-1');
ini_set('date.timezone', 'Europe/Budapest');
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);

require_once 'config.php';
require_once 'auth_helper.php';

function saveLogin($con, $id)
{
    $data = [
        'last_login' => date('Y-m-d H:i:s'),
        'id' => (int) $id,
    ];
    $sql = "UPDATE query_users SET last_login=:last_login WHERE id=:id";
    $stmt = $con->prepare($sql);
    $stmt->execute($data);
    return true;
}

$name = $_POST['logName'];
$paswdr = $_POST['paswdr'];

if(!isset($name, $paswdr)) {
    echo json_encode("Helytelen bejelentkezés.");
    return;
}

$sql = "SELECT *
      FROM query_users u
      WHERE u.name=:name";
$stmt = $con->prepare($sql);
$stmt->execute([':name' => $name]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$result || md5($paswdr) !== $result['password']) {
    header('Content-Type: application/json');
    echo json_encode("Helytelen bejelentkezés.");
    return;
}

// Sikeres bejelentkezés
saveLogin($con, $result['id']);

// Létrehozunk egy remember tokent
$remember_token = createRememberToken($con, $result['id']);

// Cookie beállítása a régi formátumban (PHP 7.3 előtti verzióhoz)
if (!headers_sent()) {
    $expire = time() + (24 * 60 * 60);
    $cookieSet = setcookie(
        'remember_token',    // név
        $remember_token,     // érték
        $expire,            // lejárat
        '/',               // path
        '',                // domain
        true,              // secure (HTTPS esetén true)
        true               // httponly
    );
    
    if (!$cookieSet) {
        error_log('Cookie beállítása sikertelen');
    }
}

// Session változók beállítása
$_SESSION["timeout"] = time() + (24 * 60 * 60);
$_SESSION['query_user_id'] = $result['id'];
$_SESSION['query_user'] = $name;
$_SESSION['rights'] = $result['rights'];
$_SESSION['full_name'] = $result['full_name'];
$_SESSION['email'] = $result['email'];
$_SESSION['telephone'] = $result['telephone'];

header('Content-Type: application/json');
echo json_encode("ok");
return;
?>