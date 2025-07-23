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
ini_set('memory_limit', '-1');
ini_set('date.timezone', 'Europe/Budapest');
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);

/**
 * Eltávolítja a zavaró speciális karaktereket a szövegből
 * - Nulla szélességű szóköz (U+200B)
 * - Nem törő szóköz (U+00A0)
 * 
 * @param string $text A tisztítandó szöveg
 * @return string A megtisztított szöveg
 */
function removeSpecialSpaces($text) {
    // A nulla szélességű szóköz (U+200B) eltávolítása
    $text = str_replace("\xE2\x80\x8B", '', $text);
    
    // A nem törő szóköz (U+00A0) eltávolítása
    $text = str_replace("\xC2\xA0", ' ', $text);  // Helyettesítés normál szóközzel
    
    return $text;
}

/* create a dom document with encoding utf8 */
$domtree = new DOMDocument('1.0', 'UTF-8');

// Az XML entitásokhoz szükséges deklaráció
$domtree->formatOutput = true;

/* create the root element of the xml tree */
$xmlRoot = $domtree->createElement("jobs");
/* append it to the document created */
$xmlRoot = $domtree->appendChild($xmlRoot);
$jobs = getOffers($con);

/* you should enclose the following lines in a loop */
foreach($jobs as $job) {
    if($job['city_id'] === NULL) {
        continue;
    }
    $prop = getPropertis($con, $job['id']);
    $JobForm = extractArray(array_filter($prop, function($item){
        if($item['type'] == 1){
            return $item;
        }
    }))[1];

    $tasks = array_filter($prop, function($item){
        if($item['type'] == 27){
            return $item;
        }
    });

    $return_text = '<p>'.$job['lead'].'</p>';
    $return_text = $return_text."Feladatok:<br><ul>";
    foreach($tasks as $task) {
        $return_text =  $return_text . '<li>'. $task['value'].'</li>';
    }
    $return_text = $return_text.'</ul><br>Amit kínálunk:<br><ul>';
    $details = array_filter($prop, function($item){
        if($item['type'] == 20){
            return $item;
        }
    });
    foreach($details as $detail) {
        $return_text =  $return_text . '<li>'. $detail['value'].'</li>';
    }
    $return_text = $return_text.'</ul><br>Elvárások:<br><ul>';
    $expects = array_filter($prop, function($item){
        if($item['type'] == 19){
            return $item;
        }
    });
    foreach($expects as $expect) {
        $return_text =  $return_text . '<li>'. $expect['value'].'</li>';
    }
    $return_text = $return_text.'</ul>';
    $sExp = array_filter($prop, function($item){
        if($item['type'] == 6){
            return $item;
        }
    });
    if(count($sExp) > 0) {
        $return_text = $return_text.'<b>Szoftver ismeret:</b><ul>'; 
        foreach($sExp as $expect) {
            $return_text =  $return_text . '<li>'. $expect['value'].'</li>';
        }
        $return_text = $return_text.'</ul><br>';
    }
    $pStre = array_filter($prop, function($item){
        if($item['type'] == 22){
            return $item;
        }
    });
    if(count($pStre) > 0) {
        $return_text = $return_text.'<b>Személyes kompetenciák:</b><ul>'; 
        foreach($pStre as $expect) {
            $return_text =  $return_text . '<li>'. $expect['value'].'</li>';
        }
        $return_text = $return_text.'</ul><br>';
    }

    $county = getCounty($con, $job['city_id']);
    
    // Megtisztítjuk a return_text változót az összeállítás után
    $return_text = removeSpecialSpaces($return_text);

    // XML elemek létrehozása és megfelelő CDATA kezelés
    $currentTrack = $domtree->createElement("job");
    $currentTrack->setAttribute('id', $job['id']);
    $currentTrack = $xmlRoot->appendChild($currentTrack);
    
    // Linkhez CDATA elem helyes megadása
    $linkElement = $domtree->createElement('link');
    $linkCDATA = $domtree->createCDATASection(removeSpecialSpaces("https://mumi.hu/hu/allas/".$job['slug']));
    $linkElement->appendChild($linkCDATA);
    $currentTrack->appendChild($linkElement);
    
    // Névhez CDATA elem
    $nameElement = $domtree->createElement('name');
    $nameCDATA = $domtree->createCDATASection(removeSpecialSpaces($job['title']));
    $nameElement->appendChild($nameCDATA);
    $currentTrack->appendChild($nameElement);
    
    // Régióhoz CDATA elem
    $regionElement = $domtree->createElement('region');
    $regionCDATA = $domtree->createCDATASection(removeSpecialSpaces($job['city'].", ".$county['county']));
    $regionElement->appendChild($regionCDATA);
    $currentTrack->appendChild($regionElement);
    
    // Leíráshoz CDATA elem
    $descElement = $domtree->createElement('description');
    $descCDATA = $domtree->createCDATASection($return_text);
    $descElement->appendChild($descCDATA);
    $currentTrack->appendChild($descElement);
    
    // Céghez CDATA elem
    $companyElement = $domtree->createElement('company');
    $companyCDATA = $domtree->createCDATASection(removeSpecialSpaces('Munkalehetőség Mindenkinek Kft.'));
    $companyElement->appendChild($companyCDATA);
    $currentTrack->appendChild($companyElement);
    
    // Cég logóhoz CDATA elem
    $logoElement = $domtree->createElement('company_logo');
    $logoCDATA = $domtree->createCDATASection(removeSpecialSpaces('https://mumi.hu/images/logo/mumi_logo-15x40.jpg'));
    $logoElement->appendChild($logoCDATA);
    $currentTrack->appendChild($logoElement);
    
    // Publikálás dátumához CDATA elem
    $pubdateElement = $domtree->createElement('pubdate');
    $pubdateCDATA = $domtree->createCDATASection(removeSpecialSpaces(date('d.m.Y', strtotime($job['created_at']))));
    $pubdateElement->appendChild($pubdateCDATA);
    $currentTrack->appendChild($pubdateElement);
    
    // Frissítés dátumához CDATA elem
    $updatedElement = $domtree->createElement('updated');
    $updatedCDATA = $domtree->createCDATASection(removeSpecialSpaces(date('d.m.Y', strtotime($job['updated_at']))));
    $updatedElement->appendChild($updatedCDATA);
    $currentTrack->appendChild($updatedElement);
    
    // Lejárati dátumához CDATA elem
    $expireElement = $domtree->createElement('expire');
    $expireCDATA = $domtree->createCDATASection(removeSpecialSpaces(date('d.m.Y', strtotime($job['expire_date']))));
    $expireElement->appendChild($expireCDATA);
    $currentTrack->appendChild($expireElement);
    
    // Munka típusához CDATA elem
    $typeElement = $domtree->createElement('job_type');
    $typeCDATA = $domtree->createCDATASection(removeSpecialSpaces($JobForm));
    $typeElement->appendChild($typeCDATA);
    $currentTrack->appendChild($typeElement);
}

// XML mentése fájlba
$domtree->save('../xml/jooble.xml');

$return_data = json_encode("ok");
echo $return_data;


function getOffers($con) {
    $sql="
        SELECT *, (SELECT d.value FROM offer_dictionary_relation odr INNER JOIN dictionary d ON d.id=odr.dictionary_id WHERE d.dictionary_type=9 AND odr.offer_id=o.id LIMIT 1) as 'city', (SELECT odr.dictionary_id FROM offer_dictionary_relation odr INNER JOIN dictionary d ON d.id=odr.dictionary_id WHERE d.dictionary_type=9 AND odr.offer_id=o.id LIMIT 1) as 'city_id'
        FROM offer o
        WHERE status=5 AND expire_date > '".date('Y-m-d')."'";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Tisztítsd meg az eredményeket
    foreach ($result as &$row) {
        foreach ($row as $key => &$value) {
            if (is_string($value)) {
                $value = removeSpecialSpaces($value);
            }
        }
    }
    
    return $result;
}

function getPropertis($con, $offer_id) {
    $sql="
        SELECT odr.id, (SELECT d.value FROM dictionary d WHERE d.id=odr.dictionary_id) as 'value', (SELECT d.dictionary_type FROM dictionary d WHERE d.id=odr.dictionary_id) as 'type'
        FROM offer_dictionary_relation odr
        WHERE odr.offer_id=$offer_id";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Tisztítsd meg az eredményeket
    foreach ($result as &$row) {
        foreach ($row as $key => &$value) {
            if (is_string($value)) {
                $value = removeSpecialSpaces($value);
            }
        }
    }
    
    return $result;
}

function getCounty($con, $city_id) {
    $sql="
        SELECT (SELECT d.value FROM dictionary d WHERE d.id=dl.county_id) as 'county'
        FROM dic_location dl
        WHERE dl.city_id=$city_id";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Tisztítsd meg az eredményeket
    if (is_array($result)) {
        foreach ($result as $key => &$value) {
            if (is_string($value)) {
                $value = removeSpecialSpaces($value);
            }
        }
    }
    
    return $result;
}

function extractArray($array) {
   $final = [];
   foreach ($array as $arr) {
       foreach ($arr as $block) {
           $final[] = $block;
       }
   }
   return $final;
}