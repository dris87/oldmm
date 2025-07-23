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

// Config betöltése először
require_once 'config.php';
require_once 'auth_helper.php';

// Ellenőrizzük, hogy van-e aktív kapcsolat és user ID
if (isset($_SESSION['query_user_id']) && $con) {
    try {
        clearRememberToken($con, $_SESSION['query_user_id']);
        
        // Cookie törlése a régi formátumban
        setcookie(
            'remember_token',    // név
            '',                 // üres érték
            time() - 3600,      // lejárati idő (múltbeli)
            '/',               // path
            '',                // domain
            true,              // secure (HTTPS esetén true)
            true               // httponly
        );

        // Session törlése
        session_destroy();
        
        header('Content-Type: application/json');
        echo json_encode("ok");
        return;
        
    } catch (Exception $e) {
        error_log('Logout error: ' . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode("error");
        return;
    }
} else {
    // Ha nincs session vagy connection, akkor is töröljük amit lehet
    setcookie(
        'remember_token',
        '',
        time() - 3600,
        '/',
        '',
        true,
        true
    );
    session_destroy();
    
    header('Content-Type: application/json');
    echo json_encode("ok");
    return;
}