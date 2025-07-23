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
    // Keresési paraméter és lapozás feldolgozása
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 20; // elemek száma oldalanként
    $offset = ($page - 1) * $limit;

    // Cache kulcs generálása a paraméterek alapján
    $cacheKey = md5($search . '_' . $page);
    $cacheFile = sys_get_temp_dir() . '/offer_list_cache_' . $cacheKey . '.json';
    $cacheTime = 300; // 5 perc
    
    // Cache ellenőrzése
    if (file_exists($cacheFile)) {
        $cachedData = json_decode(file_get_contents($cacheFile), true);
        if (isset($cachedData['timestamp']) && 
            (time() - $cachedData['timestamp'] < $cacheTime)) {
            return $cachedData['data'];
        }
    }
    
    // SQL módosítása keresés és lapozás támogatásához
    $sql = "
        SELECT 
            o.id as id,  -- Select2 az 'id'-t használja értékként
            CONCAT(f.name,' - ', o.title) as text,  -- Select2 a 'text'-et használja megjelenítésre
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
        )
        AND (
            f.name LIKE :search 
            OR o.title LIKE :search
        )
        LIMIT :limit OFFSET :offset";
        
    $stmt = $con->prepare($sql);
    $searchTerm = '%' . $search . '%';
    $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Összes találat számolása lapozáshoz
    $countSql = "
        SELECT COUNT(*) 
        FROM offer o
        INNER JOIN firm f ON f.id = o.firm_id
        WHERE EXISTS (
            SELECT 1 
            FROM query_employees qe 
            WHERE qe.offer_id = o.id
        )
        AND (
            f.name LIKE :search 
            OR o.title LIKE :search
        )";
    
    $countStmt = $con->prepare($countSql);
    $countStmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
    $countStmt->execute();
    $totalCount = $countStmt->fetchColumn();
    
    // Select2 formátumú válasz összeállítása
    $response = [
        'items' => $results,
        'pagination' => [
            'more' => ($offset + $limit) < $totalCount
        ]
    ];
    
    // Cache mentése
    $cacheData = [
        'timestamp' => time(),
        'data' => $response
    ];
    file_put_contents($cacheFile, json_encode($cacheData));
    
    return $response;
}