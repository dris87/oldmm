<?php
// auth_helper.php

function generateRememberToken() {
    try {
        return bin2hex(random_bytes(32));
    } catch (Exception $e) {
        error_log('Hiba a token generálásánál: ' . $e->getMessage());
        return false;
    }
}

function createRememberToken($con, $userId) {
    try {
        $token = generateRememberToken();
        if (!$token) {
            error_log('Token generálás sikertelen');
            return false;
        }

        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $sql = "UPDATE query_users 
                SET remember_token = :token,
                    remember_token_expires_at = :expires
                WHERE id = :userId";
                
        $stmt = $con->prepare($sql);
        $result = $stmt->execute([
            ':token' => $token,
            ':expires' => $expires,
            ':userId' => (int)$userId
        ]);
        
        if (!$result) {
            error_log('Sikertelen token mentés az adatbázisba');
            return false;
        }

        if ($stmt->rowCount() === 0) {
            error_log('Nem található felhasználó ezzel az ID-val: ' . $userId);
            return false;
        }
        
        return $token;
    } catch (PDOException $e) {
        error_log('Adatbázis hiba a token létrehozásánál: ' . $e->getMessage());
        return false;
    }
}

function validateRememberToken($con, $token) {
    try {
        if (empty($token)) {
            error_log('Üres token érkezett validálásra');
            return false;
        }
        
        $sql = "SELECT * FROM query_users 
                WHERE remember_token = :token 
                AND remember_token_expires_at > NOW()";
                
        $stmt = $con->prepare($sql);
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            error_log('Érvénytelen vagy lejárt token');
            return false;
        }
        
        // Token megújítása
        $newToken = createRememberToken($con, $user['id']);
        if (!$newToken) {
            error_log('Token megújítása sikertelen');
            return false;
        }
        
        // Session beállítása
        $_SESSION["timeout"] = time() + (24 * 60 * 60);
        $_SESSION['query_user_id'] = $user['id'];
        $_SESSION['query_user'] = $user['name'];
        $_SESSION['rights'] = $user['rights'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['telephone'] = $user['telephone'];
        
        return [
            'token' => $newToken,
            'user' => $user
        ];
    } catch (PDOException $e) {
        error_log('Adatbázis hiba a token validálásánál: ' . $e->getMessage());
        return false;
    }
}

function clearRememberToken($con, $userId) {
    try {
        $sql = "UPDATE query_users 
                SET remember_token = NULL,
                    remember_token_expires_at = NULL 
                WHERE id = :userId";
                
        $stmt = $con->prepare($sql);
        $result = $stmt->execute([':userId' => (int)$userId]);
        
        if (!$result) {
            error_log('Token törlése sikertelen: ' . $userId);
            return false;
        }
        
        return true;
    } catch (PDOException $e) {
        error_log('Adatbázis hiba a token törlésekor: ' . $e->getMessage());
        return false;
    }
}
?>