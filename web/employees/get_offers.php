<?php
require_once 'config.php';
require_once 'auth_helper.php';

// Session kezelés ellenőrzéssel
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

header('Content-Type: application/json');

// Meghívjuk a getOfferList függvényt
$offers = getOfferList($con);

echo json_encode($offers);


// Keresd meg a getOfferList függvényt a kódban (kb. a 182. sor körül)
// És cseréld le az egész függvényt erre:

function getOfferList($con) {
    $cacheFile = sys_get_temp_dir() . '/offer_list_cache.json';
    $cacheTime = 300; // 5 perc
    
    // Ellenőrizzük a fájl cache-t
    if (file_exists($cacheFile)) {
        $cachedData = json_decode(file_get_contents($cacheFile), true);
        
        // Ellenőrizzük hogy a cache 5 percnél frissebb-e
        if (isset($cachedData['timestamp']) && 
            (time() - $cachedData['timestamp'] < $cacheTime)) {
            return $cachedData['data'];
        }
    }
    
    // Ha nincs érvényes cache, lekérjük az adatokat
    $sql = "
        SELECT 
            o.id as offer_id,
            CONCAT(f.name,' - ', o.title) as 'offer',
            (
                SELECT d.value 
                FROM offer_dictionary_relation odr 
                INNER JOIN dictionary d ON d.id = odr.dictionary_id 
                WHERE d.dictionary_type = 9 
                AND odr.offer_id = o.id 
                LIMIT 1
            ) as city
        FROM offer o
        INNER JOIN firm f ON f.id = o.firm_id
        WHERE EXISTS (
            SELECT 1 
            FROM query_employees qe 
            WHERE qe.offer_id = o.id
        )";
        
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Cache-eljük az eredményt fájlban
    $cacheData = [
        'timestamp' => time(),
        'data' => $results
    ];
    
    file_put_contents($cacheFile, json_encode($cacheData));
    
    return $results;
}