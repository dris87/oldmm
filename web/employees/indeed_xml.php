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
 * Eltávolítja a zavaró speciális karaktereket és HTML entitásokat a szövegből
 * - Nulla szélességű szóköz (U+200B)
 * - Nem törő szóköz (U+00A0)
 * - HTML entitások dekódolása
 * 
 * @param string $text A tisztítandó szöveg
 * @return string A megtisztított szöveg
 */
function removeSpecialSpaces($text) {
    // A nulla szélességű szóköz (U+200B) eltávolítása
    $text = str_replace("\xE2\x80\x8B", '', $text);
    
    // A nem törő szóköz (U+00A0) eltávolítása
    $text = str_replace("\xC2\xA0", ' ', $text);  // Helyettesítés normál szóközzel
    
    // HTML entitások dekódolása (pl. &amp; → &, &lt; → <, &gt; → >)
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    return $text;
}

/* create a dom document with encoding utf8 */
$domtree = new DOMDocument('1.0', 'UTF-8');

// Az XML entitásokhoz szükséges deklaráció
$domtree->formatOutput = true;

/* create the root element of the xml tree */
$xmlRoot = $domtree->createElement("source");
/* append it to the document created */
$xmlRoot = $domtree->appendChild($xmlRoot);

// Publisher ID hozzáadása (ezt a saját Indeed publisher ID-dre kell cserélni)
$publisherElement = $domtree->createElement("publisher");
$publisherCDATA = $domtree->createCDATASection("Munkalehetőség Mindenkinek Kft.");
$publisherElement->appendChild($publisherCDATA);
$xmlRoot->appendChild($publisherElement);

// Publisher URL hozzáadása
$publisherUrlElement = $domtree->createElement("publisherurl");
$publisherUrlCDATA = $domtree->createCDATASection("https://mumi.hu");
$publisherUrlElement->appendChild($publisherUrlCDATA);
$xmlRoot->appendChild($publisherUrlElement);

// Utolsó build dátum hozzáadása
$lastBuildDateElement = $domtree->createElement("lastBuildDate");
$lastBuildDateCDATA = $domtree->createCDATASection(date('D, d M Y H:i:s T'));
$lastBuildDateElement->appendChild($lastBuildDateCDATA);
$xmlRoot->appendChild($lastBuildDateElement);

$jobs = getOffers($con);

/* minden állás feldolgozása */
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

    // Kategóriák lekérése (type 5) - csak XML mezőkhöz, nem a leíráshoz
    $categories = array_filter($prop, function($item){
        if($item['type'] == 5){
            return $item;
        }
    });

    // Tapasztalatok lekérése (type 3)
    $experiences = array_filter($prop, function($item){
        if($item['type'] == 3){
            return $item;
        }
    });

    // Előnyök lekérése (type 17 vagy 18)
    $advantages = array_filter($prop, function($item){
        if($item['type'] == 17 || $item['type'] == 18){
            return $item;
        }
    });

    // Állásleírás összeállítása - pont ugyanúgy mint a weboldalon
    $description = '<p>'.$job['lead'].'</p>';
    
    // Feladatok
    if(count($tasks) > 0) {
        $description = $description."<br><strong>Feladatok:</strong><br><ul>";
        foreach($tasks as $task) {
            $description = $description . '<li>'. $task['value'].'</li>';
        }
        $description = $description.'</ul>';
    }
    
    $details = array_filter($prop, function($item){
        if($item['type'] == 20){
            return $item;
        }
    });
    
    if(count($details) > 0) {
        $description = $description.'<br><strong>Amit kínálunk:</strong><br><ul>';
        foreach($details as $detail) {
            $description = $description . '<li>'. $detail['value'].'</li>';
        }
        $description = $description.'</ul>';
    }
    
    $expects = array_filter($prop, function($item){
        if($item['type'] == 19){
            return $item;
        }
    });
    
    if(count($expects) > 0) {
        $description = $description.'<br><strong>Elvárások:</strong><br><ul>';
        foreach($expects as $expect) {
            $description = $description . '<li>'. $expect['value'].'</li>';
        }
        $description = $description.'</ul>';
    }
    
    $sExp = array_filter($prop, function($item){
        if($item['type'] == 6){
            return $item;
        }
    });
    
    if(count($sExp) > 0) {
        $description = $description.'<br><strong>Szoftver ismeret:</strong><ul>'; 
        foreach($sExp as $expect) {
            $description = $description . '<li>'. $expect['value'].'</li>';
        }
        $description = $description.'</ul>';
    }
    
    $pStre = array_filter($prop, function($item){
        if($item['type'] == 22){
            return $item;
        }
    });
    
    if(count($pStre) > 0) {
        $description = $description.'<br><strong>Személyes kompetenciák:</strong><ul>'; 
        foreach($pStre as $expect) {
            $description = $description . '<li>'. $expect['value'].'</li>';
        }
        $description = $description.'</ul>';
    }
    
    if(count($experiences) > 0) {
        $description = $description.'<br><strong>Szükséges tapasztalat:</strong><ul>'; 
        foreach($experiences as $exp) {
            $description = $description . '<li>'. $exp['value'].'</li>';
        }
        $description = $description.'</ul>';
    }
    
    // Előnyök hozzáadása (ami hiányzott)
    if(count($advantages) > 0) {
        $description = $description.'<br><strong>Előnyök:</strong><ul>'; 
        foreach($advantages as $advantage) {
            $description = $description . '<li>'. $advantage['value'].'</li>';
        }
        $description = $description.'</ul>';
    }
    
    // A "Hirdetés adatai" szekció adatainak hozzáadása a leíráshoz
    // (ahogy az Indeed kéri, hogy a dedikált tagekben lévő adatok is legyenek a description-ben)
    $description = $description.'<br><br><strong>Hirdetés adatai:</strong><br>';
    $description = $description.'<strong>Munkavégzés helye:</strong> '.$job['city'].', '.$county['county'].'<br>';
    if (!empty($JobForm)) {
        $description = $description.'<strong>Munkavégzés típusa:</strong> '.$JobForm.'<br>';
    }
    if (!empty($job['numberOfEmployee'])) {
        $description = $description.'<strong>Létszám:</strong> '.$job['numberOfEmployee'].' fő<br>';
    }
    $description = $description.'<strong>Jelentkezési határidő:</strong> '.date('Y.m.d', strtotime($job['expire_date'])).'<br>';

    $county = getCounty($con, $job['city_id']);
    
    // Megtisztítjuk a leírást az összeállítás után
    $description = removeSpecialSpaces($description);

    // Job elem létrehozása
    $jobElement = $domtree->createElement("job");
    $xmlRoot->appendChild($jobElement);
    
    // Cím (title)
    $titleElement = $domtree->createElement('title');
    $titleCDATA = $domtree->createCDATASection(removeSpecialSpaces($job['title']));
    $titleElement->appendChild($titleCDATA);
    $jobElement->appendChild($titleElement);
    
    // Dátum (date)
    $dateElement = $domtree->createElement('date');
    $dateCDATA = $domtree->createCDATASection(removeSpecialSpaces(date('D, d M Y H:i:s T', strtotime($job['created_at']))));
    $dateElement->appendChild($dateCDATA);
    $jobElement->appendChild($dateElement);
    
    // Referencia szám (referencenumber)
    $refElement = $domtree->createElement('referencenumber');
    $refCDATA = $domtree->createCDATASection(removeSpecialSpaces('MUMI-'.$job['id']));
    $refElement->appendChild($refCDATA);
    $jobElement->appendChild($refElement);
    
    // Requisition ID (kötelező az Indeed számára)
    $reqElement = $domtree->createElement('requisitionid');
    $reqCDATA = $domtree->createCDATASection(removeSpecialSpaces('MUMI-REQ-'.$job['id']));
    $reqElement->appendChild($reqCDATA);
    $jobElement->appendChild($reqElement);
    
    // URL (Indeed tracking paraméterrel)
    $urlElement = $domtree->createElement('url');
    $jobUrl = "https://mumi.hu/hu/allas/".$job['slug']."?source=Indeed";
    $urlCDATA = $domtree->createCDATASection(removeSpecialSpaces($jobUrl));
    $urlElement->appendChild($urlCDATA);
    $jobElement->appendChild($urlElement);
    
    // Cég neve (company)
    $companyElement = $domtree->createElement('company');
    $companyCDATA = $domtree->createCDATASection(removeSpecialSpaces('Munkalehetőség Mindenkinek Kft.'));
    $companyElement->appendChild($companyCDATA);
    $jobElement->appendChild($companyElement);
    
    // Város (city)
    $cityElement = $domtree->createElement('city');
    $cityCDATA = $domtree->createCDATASection(removeSpecialSpaces($job['city']));
    $cityElement->appendChild($cityCDATA);
    $jobElement->appendChild($cityElement);
    
    // Állam/Megye (state)
    $stateElement = $domtree->createElement('state');
    $stateCDATA = $domtree->createCDATASection(removeSpecialSpaces($county['county']));
    $stateElement->appendChild($stateCDATA);
    $jobElement->appendChild($stateElement);
    
    // Ország (country)
    $countryElement = $domtree->createElement('country');
    $countryCDATA = $domtree->createCDATASection('HU');
    $countryElement->appendChild($countryCDATA);
    $jobElement->appendChild($countryElement);
    
    // Email (kötelező Indeed számára - cseréld le a valós email címre)
    $emailElement = $domtree->createElement('email');
    $emailCDATA = $domtree->createCDATASection('info@mumi.hu');
    $emailElement->appendChild($emailCDATA);
    $jobElement->appendChild($emailElement);
    
    // Irányítószám (ha van)
    // $postalcodeElement = $domtree->createElement('postalcode');
    // $postalcodeCDATA = $domtree->createCDATASection('');
    // $postalcodeElement->appendChild($postalcodeCDATA);
    // $jobElement->appendChild($postalcodeElement);
    
    // Leírás (description)
    $descElement = $domtree->createElement('description');
    $descCDATA = $domtree->createCDATASection($description);
    $descElement->appendChild($descCDATA);
    $jobElement->appendChild($descElement);
    
    // Fizetés (ha van)
    // $salaryElement = $domtree->createElement('salary');
    // $salaryCDATA = $domtree->createCDATASection('');
    // $salaryElement->appendChild($salaryCDATA);
    // $jobElement->appendChild($salaryElement);
    
    // Oktatás (ha van)
    // $educationElement = $domtree->createElement('education');
    // $educationCDATA = $domtree->createCDATASection('');
    // $educationElement->appendChild($educationCDATA);
    // $jobElement->appendChild($educationElement);
    
    // Munka típusa (jobtype) - Indeed formátumra konvertálva
    $indeedJobType = convertToIndeedJobType($JobForm);
    if (!empty($indeedJobType)) {
        $jobtypeElement = $domtree->createElement('jobtype');
        $jobtypeCDATA = $domtree->createCDATASection(removeSpecialSpaces($indeedJobType));
        $jobtypeElement->appendChild($jobtypeCDATA);
        $jobElement->appendChild($jobtypeElement);
    }
    
    // Kategória (ha van)
    if(count($categories) > 0) {
        $categoryElement = $domtree->createElement('category');
        $categoryValues = array_map(function($cat) { return $cat['value']; }, $categories);
        $categoryCDATA = $domtree->createCDATASection(removeSpecialSpaces(implode(', ', $categoryValues)));
        $categoryElement->appendChild($categoryCDATA);
        $jobElement->appendChild($categoryElement);
    }
    
    // Tapasztalat (ha van)
    if(count($experiences) > 0) {
        $experienceElement = $domtree->createElement('experience');
        $expValues = array_map(function($exp) { return $exp['value']; }, $experiences);
        $experienceCDATA = $domtree->createCDATASection(removeSpecialSpaces(implode(', ', $expValues)));
        $experienceElement->appendChild($experienceCDATA);
        $jobElement->appendChild($experienceElement);
    }
    
    // Lejárati dátum (expirationdate) - ISO format
    if (!empty($job['expire_date'])) {
        $expireElement = $domtree->createElement('expirationdate');
        $expireCDATA = $domtree->createCDATASection(removeSpecialSpaces(date('Y-m-d', strtotime($job['expire_date']))));
        $expireElement->appendChild($expireCDATA);
        $jobElement->appendChild($expireElement);
    }
}

// XML mentése fájlba
$domtree->save('../xml/indeed.xml');

$return_data = json_encode("ok");
echo $return_data;

// Függvények (ugyanazok, mint a Jooble-nél)
function getOffers($con) {
    $sql="
        SELECT o.*, 
               (SELECT d.value FROM offer_dictionary_relation odr INNER JOIN dictionary d ON d.id=odr.dictionary_id WHERE d.dictionary_type=9 AND odr.offer_id=o.id LIMIT 1) as 'city', 
               (SELECT odr.dictionary_id FROM offer_dictionary_relation odr INNER JOIN dictionary d ON d.id=odr.dictionary_id WHERE d.dictionary_type=9 AND odr.offer_id=o.id LIMIT 1) as 'city_id'
        FROM offer o
        WHERE o.status=5 AND o.expire_date > :current_date";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':current_date', date('Y-m-d'), PDO::PARAM_STR);
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
        WHERE odr.offer_id=:offer_id";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':offer_id', $offer_id, PDO::PARAM_INT);
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
        WHERE dl.city_id=:city_id LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':city_id', $city_id, PDO::PARAM_INT);
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

/**
 * Magyar munkavégzés típusokat Indeed formátumra konvertálja
 * 
 * @param string $hungarianJobType Magyar munkavégzés típus
 * @return string|null Indeed jobtype érték vagy null ha nem megfelelthető
 */
function convertToIndeedJobType($hungarianJobType) {
    if (empty($hungarianJobType)) {
        return null;
    }
    
    $hungarianJobType = strtolower(trim($hungarianJobType));
    
    // Teljes munkaidős típusok
    $fullTimeTypes = [
        'alkalmazott',
        'alkalmazotti jogviszony',
        'teljes munkaidő',
        'teljes munkaidős',
        'főállás',
        'főállású',
        '8 órás munkaidő',
        'hagyományos munkaidő'
    ];
    
    // Részmunkaidős típusok
    $partTimeTypes = [
        'részmunkaidő',
        'részmunkaidős',
        'megbízásos jogviszony',
        'megbízás',
        'vállalkozói jogviszony',
        'vállalkozó',
        'alkalmi munka',
        'gyakornoki jogviszony',
        'gyakornok',
        'diákmunka',
        'diák',
        'szerződéses',
        'projekt alapú',
        'részidős',
        'hétvégi munka',
        'alkalmi',
        'órabér',
        'kiegészítő tevékenység'
    ];
    
    // Teljes munkaidő ellenőrzése
    foreach ($fullTimeTypes as $type) {
        if (strpos($hungarianJobType, $type) !== false) {
            return 'fulltime';
        }
    }
    
    // Részmunkaidő ellenőrzése  
    foreach ($partTimeTypes as $type) {
        if (strpos($hungarianJobType, $type) !== false) {
            return 'parttime';
        }
    }
    
    // Távmunka speciális kezelése - lehet teljes vagy részmunkaidős
    if (strpos($hungarianJobType, 'távmunka') !== false || 
        strpos($hungarianJobType, 'otthoni munka') !== false ||
        strpos($hungarianJobType, 'home office') !== false) {
        // Alapértelmezetten teljes munkaidő, ha nincs más jelzés
        return 'fulltime';
    }
    
    // Ha nem sikerült megfeleltetni, visszatérünk null-lal
    return null;
}
?>