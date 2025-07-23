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
error_reporting(0);
ini_set('display_errors', 0);

$cities = getCities($con);
$categories = getCategories($con);
$counties = getCounties($con);
$yesterday = date('Y-m-d',strtotime("-1 days"));

$offerList = getOfferList($con);

$driving_licenses = getDrivingLicences($con);
$languagesArray = getLanguages($con);
$languages = [];

foreach($languagesArray as $language) {
    foreach($levels as $level) {
        $languages[] = [
            "value" => $language['value']." - ".$level
        ];
    }
}


function getCities($con) {
    $sql="
        SELECT d.id, d.value
        FROM dictionary d
        WHERE status=1 AND dictionary_type=9";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
}

function getCategories($con) {
    $sql="
        SELECT d.id, d.value
        FROM dictionary d
        WHERE status=1 AND dictionary_type=5";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
}

function getCounties($con) {
    $sql="
        SELECT d.id, d.value
        FROM dictionary d
        WHERE status=1 AND dictionary_type=8";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
}

function getDrivingLicences($con) {
    $sql="
        SELECT d.id, d.value
        FROM dictionary d
        WHERE status=1 AND dictionary_type=13";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
} 

function getLanguages($con) {
    $sql="
        SELECT d.id, d.value
        FROM dictionary d
        WHERE status=1 AND dictionary_type=7";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
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

?>

<html>
<head>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js" integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css" integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"> 
<script src="https://cdn.datatables.net/2.0.3/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.1/js/responsive.dataTables.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.1/js/dataTables.responsive.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.1/css/responsive.dataTables.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.bootstrap4.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.1/css/responsive.bootstrap4.css">
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<title>Jelentkezők listája - mumi.hu</title>
<style>
input {
    border: 1px solid #ccc;
    padding: 6px 12px;
    display: inline-block;
    width: 100%;
    overflow: hidden;
    position: relative;
    z-index: 1;
    font-size: 12px !important;
}

select {
    font-size: 12px !important;
}
.menu {
    ul {
      list-style-type: none;
      overflow: hidden;
      margin: 0 0 0 15px;
      padding: 0;
      display: flex;
      align-items: center;
      font-size: 12px;
    }

    li {
      float: left;
       font-size: 12px;
    }

    li a {
      display: block;
      color: #414042;
      text-align: center;
      padding: 12px;
      text-decoration: none;
      font-weight: bold;
      border-radius: 5px;
      font-size: 12px;text-transform: uppercase;
    }

    li a:hover {
        color: white;
      background-color: #FBA500;
      
    }

    .selected {
        color: white;
       background-color: #FBA500; 
    }

}

button {
    border-radius: 5px;
}

a {
    color: #1C75BC;
}


table.dataTable tbody td {
  vertical-align: middle;
  text-align: center !important;
}

.dataTables_wrapper .dataTables_filter input::-webkit-search-cancel-button {
  -webkit-appearance: button !important;
  -moz-appearance: button !important; 
}


/* Módosítsuk a CSS-t specifikusabbra */
.offer-select-container #select-offer {
    display: none;
}

.offer-select-container #select-offer.show {
    display: block;
}

.offer-select-container .selectize-control {
    display: none;
}

.offer-select-container .selectize-control.show {
    display: block !important;
}

.offer-select-container .selectize-control {
    padding-left: 0;
    margin-left: 0;
}

button:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    background-color: #cccccc !important;
    border-color: #cccccc !important;
}

.select2-container {
    width: 100% !important; /* Teljes szélesség */
}

.select2-container .select2-selection--single,
.select2-container .select2-selection--multiple {
    height: 38px;
    font-size: 14px !important;
}

.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    line-height: 38px;
    padding-left: 12px;
    color: #495057;
}

.select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
    padding: 8px 12px;
    font-size: 14px;
}

.select2-container--bootstrap4 .select2-results__option {
    padding: 8px 12px;
    font-size: 14px;
}

/* Az input container teljes szélességű legyen */
.offer-select-container {
    width: 100%;
    padding: 0 15px;
}

/* Select2 többszörös kiválasztás stílusai */
/* Select2 többszörös kiválasztás új stílusai */
.select2-container--bootstrap4 .select2-selection--multiple {
    border: 1px solid #ced4da;
    min-height: 32px;
    line-height: 1.2;
    font-size: 12px !important;
}

.select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered {
    padding: 0 4px;
}

.select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
    background-color: #1C75BC;
    border: 1px solid #1C75BC;
    color: #fff;
    border-radius: 3px;
    padding: 2px 6px;
    margin: 2px;
    font-size: 12px;
    display: flex;
    align-items: center;
    max-width: calc(100% - 10px);
    overflow: hidden;
    text-overflow: ellipsis;
}

.select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
    color: #fff;
    font-size: 14px;
    margin-right: 5px;
    border: none;
    background: none;
    cursor: pointer;
    padding: 0 2px;
    float: left;
    display: flex;
    align-items: center;
    line-height: 1;
}

.select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #ffd700;
}

.select2-container--bootstrap4 .select2-search--inline .select2-search__field {
    margin-top: 2px;
    font-size: 12px !important;
    font-family: 'Barlow', sans-serif;
}

/* Javított select2 dropdown stílusok */
.select2-container--bootstrap4 .select2-dropdown {
    border-color: #ced4da;
    font-size: 12px;
}

.select2-container--bootstrap4 .select2-results__option {
    padding: 4px 8px;
    font-size: 12px;
}

.select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
    background-color: #1C75BC;
}

/* Select2 törlés (x) gomb a konténer jobb oldalán */
.select2-container--bootstrap4 .select2-selection--multiple .select2-selection__clear {
    margin-right: 5px;
    font-size: 14px;
    float: right;
    cursor: pointer;
}

</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body style="font-family: 'Barlow', sans-serif;margin:20px;font-size: 12px;">
<div class="row">
    <div  class="col-8 d-flex">
        <div class="col-3">
            <img src="https://mumi.hu/images/mumi-email_logo.png" />
        </div>
        <div class="menu col-9" class="col-10" style="top: 10px;">
            <ul>
              <li style="margin-right: 5px;"><a href="index.php">Rögzítés</a></li>
              <li><a href="candidate.php"  class="selected">Jelentkezők</a></li>
              <li><a href="salesman_registrations.php">Kapcsolatfelvétel</a></li>
              <li><a href="firm.php">Cégkereső</a></li>
            </ul>
        </div>
    </div>
    <div class="col-4" style="text-align:right;top: 10px;">
        <button id="logout" style="font-size: 12px;font-family: 'Barlow', sans-serif;text-transform: uppercase;background-color:#efefef;font-weight:bold;padding: 10px;border:none;cursor:pointer;">Kijelentkezés</button>
    </div>
</div>
<div class="spinner-container row w-100">
    <div class="spinner-border text-warning mx-auto" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>
<div class="form-container container-fluid d-none">

<div class="row w-100" style="font-size: 12px;">
  <form class="w-100" id="idForm" method= "POST">
    <div class="container-fluid mt-2 border p-3 bg-light">
         <div class="row">
            <div class="col-md-3 h-100 form-group">
              <label><span style="font-weight:bold;text-transform:uppercase;">Megye:</span></label>
                <select class="form-select" id="select-counties" name="counties[]" multiple="multiple" placeholder="Válassz egy megyét...">
                     <option value="" style="padding:10px 5px;">Válassz egy megyét...</option>
                <?php 
                foreach ($counties as $county) {
                    ?>
                    <option value="<?php echo $county['value']; ?>"><?php echo $county['value']; ?></option>
                 <?php 
                }
                ?>
              </select>
            </div>
            <div class="col-md-3 h-100 form-group">
                <label><span style="font-weight:bold;text-transform:uppercase;">Város:</span></label>
                <select class="form-select" id="select-state" name="states[]" multiple="multiple" placeholder="Válassz egy települést...">
                     <option value="" style="padding:10px 5px;">Válassz egy települést...</option>
                    <?php 
                    foreach ($cities as $city) {
                        ?>
                        <option value="<?php echo $city['value']; ?>"><?php echo $city['value']; ?></option>
                     <?php 
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3 h-100 form-group">
              <label><span style="font-weight:bold;text-transform:uppercase;">Kategória kiválasztása:</span></label>
                <select class="form-select" id="select-categories" name="categories[]" multiple="multiple" placeholder="Válassz egy kategóriát...">
                     <option value="" style="padding:10px 5px;">Válassz egy kategóirát...</option>
                <?php 
                foreach ($categories as $category) {
                    ?>
                    <option value="<?php echo $category['value']; ?>"><?php echo $category['value']; ?></option>
                 <?php 
                }
                ?>
              </select>
            </div>
            <div class="col-md-3 h-100 form-group">
              <label><span style="font-weight:bold;text-transform:uppercase;">Státusz:</span></label>
              <select class="form-control" id="status" name="status" placeholder="Státusz ...">
                     <option value="" style="padding:10px 5px;">Mindegy</option>
                     <?php foreach($statusArray as $key => $value) {?>
                     <option value="<?= $key?>"><?= $value?></option>
                     <?php } ?>
              </select>
            </div>
        </div>
        <div class="row">
    <div class="col-md-12 form-group offer-select-container">
        <label><span style="font-weight:bold;text-transform:uppercase;">Cég hirdetés kiválasztása:</span></label>
        <div id="offer-loader-container" class="d-flex align-items-center pl-0">
            <button type="button" id="load-offers-btn" class="btn btn-primary">
                <i class="fa fa-list" aria-hidden="true"></i> Hirdetések listájának betöltése
            </button>
            <span id="loading-time-warning" class="ml-3 font-weight-bold text-danger d-none">A betöltés 30-60 másodperc is lehet.</span>
        </div>
        <select class="form-select" id="select-offer" name="offer[]" multiple="multiple" placeholder="Válassz egy hirdetést...">
            <option value="" style="padding:10px 5px;">Válassz egy hirdetést...</option>
        </select>
    </div>
</div>
        <div class="collapse" id="collapseExample">
            <div class="row">
                 <div class="col-md-6 form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Név:</span></label>
                        <input class="form-control" id="name" name="name" placeholder="Névre való keresés">
                </div>
                <div class="col-md-6 form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">E-mail:</span></label>
                        <input class="form-control" id="email" name="email" placeholder="E-mailre való keresés">
                </div>
            </div>
            <div class="row equal-height-row ">
                <div class="col-md-4 form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Telefonszám:</span></label>
                        <input  class="form-control" id="telephone" name="telephone" placeholder="Telefonszámra való keresés">
                </div>
                <div class="col-md-8 form-group">
                    <div class="container-fluid h-100">
                        <div class="row h-100">
                            <div class="col-md-4 h-100 form-group">
                                  <label><span style="font-weight:bold;text-transform:uppercase;">Forrás:</span></label>
                                    <select class="form-control" id="sources" name="sources" placeholder="Forrás ...">
                                        <option value="">Mindegy</option>
                                        <?php 
                                        foreach ($sources as $source) {
                                        ?>
                                            <option value="<?php echo $source ?>"><?php echo $source ?></option>
                                        <?php 
                                        }
                                        ?>
                                  </select>
                            </div>
                            <div class="col-md-4 h-100 form-group">
                              <label><span style="font-weight:bold;text-transform:uppercase;">Utazna-e:</span></label>
                                <select class="form-control" id="traveling" name="traveling" placeholder="Utazna-e ...">
                                     <option value="" style="padding:10px 5px;">Mindegy</option>
                                     <option value="1">Igen</option>
                                     <option value="2">Nem</option>
                              </select>
                            </div>
                             <div class="col h-100 form-group">
                              <label><span style="font-weight:bold;text-transform:uppercase;">Külföld:</span></label>
                                <select class="form-control" id="foreigner" name="foreigner" placeholder="Külföldre ...">
                                     <option value="" style="padding:10px 5px;">Mindegy</option>
                                     <option value="1">Igen</option>
                                     <option value="2">Nem</option>
                              </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                          <label><span style="font-weight:bold;text-transform:uppercase;">Születési dátum nagyobb:</span></label>
                            <input class="form-control" type="date" id="birthday_over" name="birthday_over" placeholder="Születési dátum nagyobb mint..." pattern="\d{4}-\d{2}-\d{2}">
                        </div>
                <div class="col-md-6 form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Születési dátum kisebb:</span></label>
                        <input class="form-control" type="date" id="birthday_down" name="birthday_down" placeholder="Születési dátum kisebb mint..." pattern="\d{4}-\d{2}-\d{2}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                            <label><span style="font-weight:bold;text-transform:uppercase;">Vezetői engedély kiválasztása:</span></label>
                            <select class="form-select" id="select-driving_license" name="driving_license[]" multiple="multiple" placeholder="Válassz egy vezetői engedélyt...">
                                 <option value="" style="padding:10px 5px;">Válassz egy vezetői engedélyt...</option>
                            <?php 
                            foreach ($driving_licenses as $driving_license) {
                                ?>
                                <option value="<?php echo $driving_license['value']; ?>"><?php echo $driving_license['value']; ?></option>
                             <?php 
                            }
                            ?>
                          </select>
                </div>
                <div class="col-md-6 form-group">
                        <label><span style="font-weight:bold;text-transform:uppercase;">Nyelv kiválasztása:</span></label>
                        <select class="form-select" id="select-languages" name="languages[]" multiple="multiple" placeholder="Válassz egy nyelvet...">
                             <option value="" style="padding:10px 5px;">Válassz egy nyelvet...</option>
                        <?php 
                        foreach ($languages as $language) {
                            ?>
                            <option value="<?php echo $language['value']; ?>"><?php echo $language['value']; ?></option>
                         <?php 
                        }
                        ?>
                      </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 h-100 form-group">
                  <label><span style="font-weight:bold;text-transform:uppercase;">Önéletrajz:</span></label>
                  <select class="form-control" id="cv" name="cv" placeholder="Önéletrajz ...">
                         <option value="" style="padding:10px 5px;">Mindegy</option>
                         <option value="1">Van önéletrajza</option>
                         <option value="2">Nincs önéletrajza</option>
                  </select>
                </div>
                
            </div>
       </div>
        <div class="row pb-2">
            <div class="col-auto">
                 <button class="btn btn-secondary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample" style="font-size: 12px !important;font-weight: bold;text-transform: uppercase;">
                <i class="fa fa-expand" aria-hidden="true"></i> &nbsp;Bővített szűrés
              </button>
            </div>
            <div class="col">
            </div>
        </div> 
        <div class="row align-center">
            <div class="col-auto m-auto">
                <button id="submit-button" class="p-1" style="font-family: 'Barlow', sans-serif;text-transform: uppercase;background-color: #1C75BC;color:#ffffff;font-weight:bold;padding: 10px !important;border:none;cursor:pointer;"><i class="fa fa-filter" aria-hidden="true"></i> Szűrés</button>
                <button class="p-1" style="font-family: 'Barlow', sans-serif;text-transform: uppercase;background-color: #1DA177;color:#ffffff;font-weight:bold;padding: 10px !important;border:none;cursor:pointer;" id="submit-button-2"><i class="fa fa-download" aria-hidden="true"></i> Szűrés letöltése</button>
                <button id="reset-filters" class="p-1" style="font-family: 'Barlow', sans-serif;text-transform: uppercase;background-color: #dc3545;color:#ffffff;font-weight:bold;padding: 10px !important;border:none;cursor:pointer;">
    <i class="fa fa-times" aria-hidden="true"></i> Szűrés törlése
</button>
            </div>
            <div id="loading-spinner-top" class="spinner-border text-warning d-none" role="status">
              <span class="sr-only">Loading...</span>
            </div>
            <div class="col m-auto">
                <div class="border mw-100 p-2 d-inline-block font-weight-bold bg-white" style="font-weight: normal !important;text-transform:uppercase;">
                    Összes CV: <span id="count2" style="font-weight:bold;"></span>
                </div>
            </div>
        </div>
    </div>
  </form>
</div>
<div class="row">
  <div id="result" style="display:none;">
      <div  style="display:none;padding:20px;background-color:#1C75BC; color:#ffffff;display:flex !important; ">
        <div><a id="link" href="" target="_blank" style="max-width: 500px;color:#ffffff;text-decoration:none;"> </a></div>
        <div style="margin-left:25px;font-weight:bold;"><span id="count"></span> db önéletrajz</div>
      </div>
  </div>
</div>
    <div class="row table-container">
    <div class="table-responsive">
        <table id="table" class="table table-striped table-bordered compact stripe" style="font-size:12px;"><tr><td class="text-center font-weight-bold text-danger"><span id="warning-text-filter">A szűrés gombra való kattintás után jelennek meg adatok!</span> <div id="loading-spinner" class="spinner-border text-warning d-none" role="status">
  <span class="sr-only">Loading...</span>
</div></tr></td></table>
    </div>
    </div>
</div>
  <script>

           $(document).ready(function() {
    $('.spinner-container').addClass('d-none');
    $('.form-container').fadeIn("slow").removeClass("d-none");

    // Általános selectek inicializálása
    $('#select-state, #select-counties, #select-categories, #select-languages, #select-driving_license').each(function() {
        $(this).selectize({
            sortField: 'text',
            create: false, // Megakadályozza új opciók hozzáadását
            placeholder: $(this).attr('placeholder') // Megtartja az eredeti placeholder szöveget
        });
    });

    // Offer select kezelése külön
$("#load-offers-btn").on('click', function() {
    const $btn = $(this);
    const $container = $('#offer-loader-container');
    const $select = $('#select-offer');
    const $warning = $('#loading-time-warning');
    
    // Gombok letiltása
    const $buttons = $('#submit-button, #submit-button-2, #reset-filters, [data-toggle="collapse"]');
    $buttons.prop('disabled', true).addClass('disabled');
    
    $warning.removeClass('d-none');
    
    $btn.prop('disabled', true).addClass('disabled').html(`
        <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
        Hirdetések listája betöltés alatt...
    `);

    // Select2 inicializálása azonnal
    $select.select2({
        theme: 'bootstrap4',
        placeholder: 'Válassz egy hirdetést...',
        allowClear: true,
        width: '100%',
        ajax: {
            url: 'get_offers_new.php',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term,
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.items,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        },
        minimumInputLength: 1
    });

    // Input megjelenítése először
    $select.addClass('show').closest('.selectize-control').addClass('show');
    
    // Kis késleltetés után gomb és warning eltüntetése
    setTimeout(() => {
        $('#load-offers-btn, #loading-time-warning').fadeOut(300);
        $container.fadeOut(300);
        $buttons.prop('disabled', false).removeClass('disabled');
    }, 500);
});
$("#reset-filters").click(function(e) {
    e.preventDefault();
    
    // Alap select elemek törlése
    $('select.form-control').val('');
    
    // Input mezők törlése
    $('input').val('');
    
    // Selectize elemek törlése
    ['#select-state', '#select-counties', '#select-categories', '#select-languages', '#select-driving_license', '#select-offer'].forEach(function(selector) {
        if ($(selector)[0] && $(selector)[0].selectize) {
            $(selector)[0].selectize.clear();
        }
    });
    
    // Táblázat törlése ha létezik
    if ($.fn.DataTable.isDataTable('#table')) {
        $('#table').DataTable().clear().draw();
    }
    
    // Figyelmeztető szöveg visszaállítása
    $("#warning-text-filter").removeClass("d-none");
    
    // Eredmény rész elrejtése
    $("#result").hide();
    $("#count2").text('');
});

});
            

    $("#submit-button").click(function(e){
    e.preventDefault();
    $('#submit-button').prop('disabled', true);
    $('#submit-button-2').prop('disabled', true);
    cursor_wait();
    
    $("#warning-text-filter").addClass("d-none");
    $("#loading-spinner").addClass("d-inline-block");
    $("#loading-spinner-top").addClass("d-inline-block");
    $('body').css('cursor', 'progress');
    
    // Ha már létezik egy DataTable, akkor megszüntetjük
    if ($.fn.DataTable.isDataTable('#table')) {
        $('#table').DataTable().destroy();
    }
    
    var table = $('#table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 50,
        ajax: {
            url: "process2.php?m=q",
            type: "POST",
            data: function(d) {
                // Form adatok hozzáadása a DataTables kéréshez
                var formData = {};
                $('#idForm').serializeArray().forEach(function(item) {
                    if (formData[item.name]) {
                        if (!Array.isArray(formData[item.name])) {
                            formData[item.name] = [formData[item.name]];
                        }
                        formData[item.name].push(item.value);
                    } else {
                        formData[item.name] = item.value;
                    }
                });
                return $.extend({}, d, formData);
            }
        },
        columns: [
            { data: "id", title: "ID", width: "5%" },
            { data: "status", title: "Státusz", width: "10%" },
            { data: "name", title: "Név", width: "15%" },
            { data: "email", title: "E-mail", width: "12%" },
            { data: "telephone", title: "Telefonszám", width: "8%" },
            { data: "county", title: "Megye", width: "8%" },
            { data: "city", title: "Város", width: "8%" },
            { data: "positions", title: "Pozíciók", width: "10%" },
            { data: "offer", title: "Hirdetés", width: "15%" },
            { data: "cv_url", title: "CV", width: "3%" },
            { data: "created_at", title: "Rögzítve", width: "5%" },
            { data: "updated_at", title: "Módosítva", width: "5%" },
            { data: "settings", title: "Műveletek", width: "3%" }
        ],
        order: [[0, 'desc']],
        columnDefs: [
            { className: 'dt-center', targets: '_all' }
        ],
        language: {
            processing: "Feldolgozás...",
            lengthMenu: "Megjeleníthető elemek száma oldalanként: _MENU_",
            zeroRecords: "Nincs találat",
            info: "_PAGE_. oldal a(z) _PAGES_-ből",
            infoEmpty: "Nincs egyetlen cv-sem.",
            emptyTable: "Nincs egyetlen cv-sem.",
            infoFiltered: "(szűrt elemek _MAX_ )",
            search: 'Keresés',
            paginate: {
                first: "Első",
                previous: "Előző",
                next: "Következő",
                last: "Utolsó"
            }
        },
        drawCallback: function(settings) {
            // Számláló frissítése - most a filteredRecords értéket használjuk
            $('#count2').text(settings.json.recordsFiltered);
            
            // Loading állapot megszüntetése
            $("#loading-spinner-top").removeClass("d-inline-block").addClass("d-none");
            $("#loading-spinner").removeClass("d-inline-block").addClass("d-none");
            $('body').css('cursor', 'default');
            $('#submit-button').prop('disabled', false);
            $('#submit-button-2').prop('disabled', false);
            remove_cursor_wait();
        }
    });
});

    $("#submit-button-2").click(function(e){
        $("#idForm").submit(function(e) {
            
            e.preventDefault();
            var form = $(this);
            $.ajax({
                type: "POST",
                url: "process2.php",
                data: form.serialize(), // serializes the form's elements.
                dataType:"JSON",
                success: function(data)
                {
                  $("#link").attr("href", data.link);
                  $("#link").html(data.link);
                  $("#count").html(data.count);
                  $("#result").show();
                  $('#submit-button').prop('disabled', false);
                  $('#submit-button-2').prop('disabled', false);
                }
            }); 
            
        });

    });


    

    $("#logout").on("click", function(e) {
        $.ajax({
            type: "POST",
            url: "logout.php",
             // serializes the form's elements.
            dataType:"JSON",
            success: function(data)
            {
                if(data === "ok") {
                    window.location.href = "https://mumi.hu/employees/index.php";
                }
                
            }
        });
        
    });

    function statusChange(select, id) {
        const cv_id = id;
        const value = select.value;
        $.ajax({
            type: "POST",
            url: "functions.php?action=status-change",
            data: { id: cv_id, value: value },  // serializes the form's elements.
            //contentType: "application/json; charset=utf-8",
            //dataType:"JSON",
            encode: true,
            success: function(data)
            {
                
            }
        });
    }

   cursor_wait = function()
    {
        // switch to cursor wait for the current element over
        var elements = $(':hover');
        if (elements.length)
        {
            // get the last element which is the one on top
            elements.last().addClass('cursor-wait');
        }
        // use .off() and a unique event name to avoid duplicates
        $('html').
        off('mouseover.cursorwait').
        on('mouseover.cursorwait', function(e)
        {
            // switch to cursor wait for all elements you'll be over
            $(e.target).addClass('cursor-wait');
        });
    }

    remove_cursor_wait = function()
    {
        $('html').off('mouseover.cursorwait'); // remove event handler
        $('.cursor-wait').removeClass('cursor-wait'); // get back to default
    }
  </script>
</body>
</html>
