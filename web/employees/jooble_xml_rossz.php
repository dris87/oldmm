<?php
session_set_cookie_params('86400');
ini_set("session.gc_maxlifetime", 86400);
ini_set("session.cookie_lifetime", 86400);
session_start([
    'cookie_lifetime' => 86400,
]);

require_once 'config.php';
ini_set('memory_limit', '-1');
ini_set('date.timezone', 'Europe/Budapest');
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

if(!$_SESSION['query_user']) {
   return;
}

$con = connectDB('localhost', 'c1_web', 'c1_web', '5DpzFiY@5');

 
    /* create a dom document with encoding utf8 */
    $domtree = new DOMDocument('1.0', 'UTF-8');

    /* create the root element of the xml tree */
    $xmlRoot = $domtree->createElement("xml");
    /* append it to the document created */
    $xmlRoot = $domtree->appendChild($xmlRoot);



    /* you should enclose the following lines in a loop */
    $currentTrack = $domtree->createElement("track");
    $currentTrack = $xmlRoot->appendChild($currentTrack);
    $currentTrack->appendChild($domtree->createElement('path','song1.mp3'));
    $currentTrack->appendChild($domtree->createElement('title','title of song1.mp3'));

    /* get the xml printed */
    echo $domtree->saveXML();


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

function getCvs($con, $cities, $categories, $counties, $name, $email, $telephone , $birthday_over , $birthday_down, $traveling, $foreigner, $languages, $drivings, $sources, $cv, $status, $offers) {


     $sql="
        SELECT * FROM query_employees
      WHERE offer_id IS NOT NULL";

   if($status !== null) {
         $sql = $sql." AND status=".$status." ";
   }

   if($name !== null) {
         $sql = $sql." AND name LIKE '%".$name."%' ";
   }

   if($email !== null) {
         $sql = $sql." AND email LIKE '%".$email."%' ";
   }

   if($telephone !== null) {
         $sql = $sql." AND telephone LIKE '%".$telephone."%' ";
   }

   if($birthday_over !== null) {
         $sql = $sql." AND birthday > '".$birthday_over."' ";
   }

   if($birthday_down !== null) {
         $sql = $sql." AND birthday < '".$birthday_down."' ";
   }

   if($traveling !== null) {
         $value = ($traveling == 1) ? 1 : 0;
         $sql = $sql." AND traveling = ".$value." ";
   }

   if($foreigner !== null) {
         $value = ($foreigner == 1) ? 1 : 0;
         $sql = $sql." AND foreigner = ".$value." ";
   }

   if($sources !== null) {
         $sql = $sql." AND source = '".$sources."' ";
   }

   if($cv !== null) {
     
      if($cv == 1) {
         $sql = $sql." AND cv_url != '' ";
      } else {
         $sql = $sql." AND cv_url = '' ";
      }
   }

   if(is_array($offers) && count($offers) > 0) {
         $sql = $sql." AND offer_id IN (".implode(", ", $offers).") ";
   }

   if(is_array($drivings) && count($drivings) > 0) {
      $sql = $sql." AND (";
      foreach($drivings as $key => $driving) {
         if (end(array_keys($drivings)) !== $key) {
            $sql = $sql." driving_license LIKE '%".$driving."%' OR";
         } else {
            $sql = $sql." driving_license LIKE '%".$driving."%') ";
         }
      }
   }

   if(is_array($languages) && count($languages) > 0) {
      $sql = $sql." AND (";
      foreach($languages as $key => $language) {
         if (end(array_keys($languages)) !== $key) {
            $sql = $sql." language LIKE '%".$language."%' OR";
         } else {
            $sql = $sql." language LIKE '%".$language."%') ";
         }
      }
   }

   if(is_array($categories) && count($categories) > 0) {
      $sql = $sql." AND (";
      foreach($categories as $key => $category) {
         if (end(array_keys($categories)) !== $key) {
            $sql = $sql." positions LIKE '%".$category."%' OR";
         } else {
            $sql = $sql." positions LIKE '%".$category."%') ";
         }
      }
   }

   if(is_array($cities) && count($cities) > 0) {
      $sql = $sql." AND (";
      foreach($cities as $key => $city) {
         if (end(array_keys($cities)) !== $key) {
            $sql = $sql." city LIKE '%".$city."%' OR";
         } else {
            $sql = $sql." city LIKE '%".$city."%') ";
         }
      }
   }

   if(is_array($counties) && count($counties) > 0) {
      $sql = $sql." AND (";
      foreach($counties as $key => $county) {
         if (end(array_keys($counties)) !== $key) {
            $sql = $sql." county LIKE '%".$county."%' OR";
         } else {
            $sql = $sql." county LIKE '%".$county."%') ";
         }
      }
    }
    //var_dump($sql);
    //exit;
    $sql= $sql." ORDER BY id DESC";

    $stmt = $con->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;

}

function getOffer($con, $offer_id) {
    $sql="
        SELECT *, (SELECT d.value FROM offer_dictionary_relation odr INNER JOIN dictionary d ON d.id=odr.dictionary_id WHERE d.dictionary_type=9 AND odr.offer_id=o.id LIMIT 1) as 'city'
        FROM offer o
        WHERE o.id=$offer_id";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
}

function getFirm($con, $firm_id) {
    if($firm_id !== NULL) {
    $sql="
        SELECT *
        FROM firm
        WHERE id=$firm_id";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;

   }

   return "";
}

