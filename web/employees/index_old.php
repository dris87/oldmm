<?php
session_set_cookie_params('86400');
ini_set("session.gc_maxlifetime", 86400);
ini_set("session.cookie_lifetime", 86400);
session_start([
    'cookie_lifetime' => 86400,
]);
if(!$_SESSION['query_user']) {
    include 'login.php';
    return;
}
require_once 'config.php';
ini_set('memory_limit', '-1');
ini_set('date.timezone', 'Europe/Budapest');
set_time_limit(0);
error_reporting(0);
ini_set('display_errors', 0);

$con = connectDB('localhost', 'c1_web', 'c1_web', '5DpzFiY@5');

$cities = getCities($con);
$categories = getCategories($con);
$counties = getCounties($con);
$yesterday = date('Y-m-d',strtotime("-1 days"));

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
<title>Rögzítés - mumi.hu</title>
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

</style>
</head>
<body style="font-family: 'Barlow', sans-serif;margin:20px;font-size: 12px;">

<div class="row">
    <div  class="col-8 d-flex">
        <div class="col-3">
            <img src="https://mumi.hu/images/mumi-email_logo.png" />
        </div>
        <div class="menu col-9" class="col-10" style="top: 10px;">
            <ul>
              <li style="margin-right: 5px;"><a href="index.php" class="selected">Rögzítés</a></li>
              <li><a href="candidate.php">Jelentkezők</a></li>
              <li><a href="salesman_registrations.php">Kapcsolatfelvétel</a></li>
              <li><a href="firm.php">Cégkereső</a></li>
              <?php 
                if(in_array($_SESSION['query_user_id'], [15, 13, 11])) {
            ?>
                <button id="jooble-generate">Jooble xml generálás</button> <span style="margin-left: 5px" >Utoljára frissítve: <?php echo date ("Y.m.d H:i:s", filemtime('../xml/jooble.xml')); ?></span>
            <?php 
                }
            ?>
            </ul>
            
        </div>

    </div>

    <div class="col-4" style="text-align:right;top: 10px;">
        
        <button id="logout" style="font-size: 12px;font-family: 'Barlow', sans-serif;text-transform: uppercase;background-color:#efefef;font-weight:bold;padding: 10px;border:none;cursor:pointer;">Kijelentkezés</button>
    </div>

</div>
<div class="container-fluid">
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
        <div class="collapse" id="collapseExample">
            <div class="row">
                 <div class="col-md-6 form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Név:</span></label>
                        <input class="form-control" id="name" name="name" placeholder="Névre való keresés">
                </div>
                <div class="col-md-6 form-group"">
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
                <div class="col-md-3 h-100 form-group">
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
                <a href="create.php" class="p-1" style="font-family: 'Barlow', sans-serif;text-transform: uppercase;background-color: #FBA500;color:#ffffff;font-weight:bold;padding: 10px !important;border:none;cursor:pointer;"><i class="fa fa-user-plus" aria-hidden="true"></i> Rögzítés</a>
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
<div id="toast-alert" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000" style="position: absolute; top: 40px; right: 20px;">
          <div class="d-flex">
            <div class="toast-body" style="color:green;font-weight:bold;"> 
            A generálás sikeres!
           </div>
          </div>
        </div>
</div>


  <script>

           $( document ).ready(function() {
             
               //$('.spinner-container').fadeOut();
               $('.spinner-container').addClass('d-none');
               $('.form-container ').fadeIn( "slow");
               $('.form-container ').removeClass( "d-none");
               //$('.table-responsive ').removeClass( "d-none");
            });

            $('#select-state').selectize({
                  sortField: 'text'
              });
               $('#select-categories').selectize({
                  sortField: 'text'
              });
               $('#select-counties').selectize({
                  sortField: 'text'
              });
               $('#select-languages').selectize({
                  sortField: 'text'
              });
               $('#select-driving_license').selectize({
                  sortField: 'text'
              });
               $('#select-cv').selectize({
                  sortField: 'text'
              });
          /*  
           $('#table').dataTable( {
                pageLength: 25,
                destroy: true,
                responsive: true,
                rowReorder: {
                    selector: 'td:nth-child(2)'
                },
                 ajax: {
                    url: 'process.php?m=q',
                    dataSrc: 'records',
                    recordsTotal: 'count',
                },
                "columns": [
                    { "data": "id", title: "ID", sortable: true },
                    { "data": "status", title: "Státusz", sortable: false},
                    { "data": "name", title: "Név", sortable: true},
                    { "data": "source", title: "Forrás", sortable: true },
                    //{ "data": "birthday", title: "Születésnap", sortable: true },
                    { "data": "email", title: "E-mail", sortable: true },
                    { "data": "telephone" , title: "Telefonszám", sortable: true },
                    { "data": "county", title: "Megye", sortable: true },
                    { "data": "city" , title: "Város", sortable: true },
                    { "data": "positions" , title: "Kategóriák", sortable: true },
                    
                    { "data": "moving" , title: "Költözne-e szállás esetén", sortable: true },
                    { "data": "traveling" , title: "Utazna-e:", sortable: true },
                    { "data": "traveling_km" , title: "Km megadása:", sortable: true },
                    { "data": "foreigner" , title: "Külföld", sortable: true },
                    { "data": "where_foreign" , title: "Külföldre hová", sortable: true },
                   
                    { "data": "cv_url" , title: "CV", sortable: false },
                    { "data": "created_at" , title: "Rögzítve", sortable: true },
                    { "data": "updated_at" , title: "Módosítva", sortable: true },
                    { "data": "settings" , title: "Műveletek", sortable: false }
                ],
                columnDefs: [
                    { className: 'dt-center', targets: '_all' }
                ],
                "language": {
                    "lengthMenu": "Megjeleníthető elemek száma oldalanként: _MENU_",
                    "zeroRecords": "Nincs találat",
                    "info": "_PAGE_. oldal a(z) _PAGES_-ből",
                    "infoEmpty": "Nincs egyetlen cv-sem.",
                    "emptyTable": "Nincs egyetlen cv-sem.",
                    "infoFiltered": "(szűrt elemek _MAX_ )",
                    "search": 'Keresés'
                },
                fixedHeader: true,
                initComplete: function() {
                $('#count2').text( this.api().data().length )
                },
                order: [[0, 'desc']]
            }); */ 

// Szűrés és táblázat kezelés
$("#submit-button").click(function(e){
    $("#idForm").submit(function(e) {
        e.preventDefault();
        $('#submit-button').prop('disabled', true);
        $('#submit-button-2').prop('disabled', true);
        cursor_wait();
        
        $("#warning-text-filter").addClass("d-none");
        $("#loading-spinner").addClass("d-inline-block");
        $("#loading-spinner-top").addClass("d-inline-block");
        $('body').css('cursor', 'progress');

        if ($.fn.DataTable.isDataTable('#table')) {
            $('#table').DataTable().destroy();
        }
        
        var table = $('#table').DataTable({
            serverSide: true,
            processing: true,
            pageLength: 25,
            destroy: true,
            responsive: true,
            rowReorder: {
                selector: 'td:nth-child(2)'
            },
            ajax: {
                url: "process.php?m=q",
                type: "POST",
                data: function(d) {
                    return $.extend({}, d, {
                        'counties': $('#select-counties').val(),
                        'states': $('#select-state').val(),
                        'categories': $('#select-categories').val(),
                        'name': $('#name').val(),
                        'email': $('#email').val(),
                        'telephone': $('#telephone').val(),
                        'birthday_over': $('#birthday_over').val(),
                        'birthday_down': $('#birthday_down').val(),
                        'traveling': $('#traveling').val(),
                        'foreigner': $('#foreigner').val(),
                        'driving_license': $('#select-driving_license').val(),
                        'languages': $('#select-languages').val(),
                        'sources': $('#sources').val(),
                        'status': $('#status').val(),
                        'cv': $('#cv').val()
                    });
                },
                dataSrc: function(json) {
                    $('#count2').text(json.recordsTotal);
                    remove_cursor_wait();
                    $("#loading-spinner-top").addClass("d-none");
                    $("#loading-spinner-top").removeClass("d-inline-block");
                    $('body').css('cursor', 'default');
                    $('#submit-button').prop('disabled', false);
                    $('#submit-button-2').prop('disabled', false);
                    $("#result").hide();
                    return json.data;
                }
            },
            columns: [
                { "data": "id", title: "ID", sortable: true },
                { "data": "status", title: "Státusz", sortable: false},
                { "data": "name", title: "Név", sortable: true},
                { "data": "source", title: "Forrás", sortable: true },
                { "data": "email", title: "E-mail", sortable: true },
                { "data": "telephone" , title: "Telefonszám", sortable: true },
                { "data": "county", title: "Megye", sortable: true },
                { "data": "city" , title: "Város", sortable: true },
                { "data": "positions" , title: "Kategóriák", sortable: true },
                { "data": "cv_url" , title: "CV", sortable: false },
                { "data": "created_at" , title: "Rögzítve", sortable: true },
                { "data": "updated_at" , title: "Módosítva", sortable: true },
                { "data": "settings" , title: "Műveletek", sortable: false }
            ],
            columnDefs: [
                { className: 'dt-center', targets: '_all' }
            ],
            language: {
                processing: "Betöltés...",
                lengthMenu: "Megjeleníthető elemek száma oldalanként: _MENU_",
                zeroRecords: "Nincs találat",
                info: "_PAGE_. oldal a(z) _PAGES_-ből",
                infoEmpty: "Nincs egyetlen cv-sem.",
                emptyTable: "Nincs egyetlen cv-sem.",
                infoFiltered: "(szűrt elemek _MAX_ )",
                search: 'keresés',
                paginate: {
                    first: "Első",
                    previous: "Előző",
                    next: "Következő",
                    last: "Utolsó"
                }
            },
            order: [[0, 'desc']]
        });

        return false;
    });
});

// Excel export gomb kezelése
$("#submit-button-2").click(function(e){
    $("#idForm").submit(function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            type: "POST",
            url: "process.php",
            data: form.serialize(),
            dataType:"JSON",
            success: function(data) {
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

    $("#jooble-generate").on("click", function(e) {
        $('body').css('cursor', 'progress');
        $( "#loading-spinner-gen" ).addClass( "d-inline-block" );
        $( "#loading-spinner-gen" ).removeClass( "d-none");
        $.ajax({
            type: "POST",
            url: "jooble_uj_xml.php",
             // serializes the form's elements.
            dataType:"JSON",
            success: function(data)
            {
        
                if(data === "ok") {
                    $( "#loading-spinner-gen" ).addClass( "d-none" );
                    $( "#loading-spinner-gen" ).removeClass( "d-inline-block" );
                    $('body').css('cursor', 'default');
                    $('#toast-alert').toast('show');
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

// Segédfüggvények
function cursor_wait() {
    var elements = $(':hover');
    if (elements.length) {
        elements.last().addClass('cursor-wait');
    }
    $('html').
    off('mouseover.cursorwait').
    on('mouseover.cursorwait', function(e) {
        $(e.target).addClass('cursor-wait');
    });
}

function remove_cursor_wait() {
    $('html').off('mouseover.cursorwait');
    $('.cursor-wait').removeClass('cursor-wait');
}
  </script>
</body>
</html>
