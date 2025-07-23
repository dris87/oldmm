<?php
session_set_cookie_params('86400');
ini_set("session.gc_maxlifetime", 86400);
ini_set("session.cookie_lifetime", 86400);
require_once 'config.php';
require_once 'auth_helper.php';

session_start();

if (!isset($_SESSION['query_user'])) {
    // Nincs aktív session, próbáljuk meg a remember token-t
    if (isset($_COOKIE['remember_token'])) {
        $auth = validateRememberToken($con, $_COOKIE['remember_token']);
        if ($auth) {
            // Frissítjük a cookie-t az új tokennel
            setcookie('remember_token', $auth['token'], time() + (24 * 60 * 60), '/', '', true, true);
        } else {
            // Érvénytelen token, töröljük a cookie-t
            setcookie('remember_token', '', time() - 3600, '/');
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

$campaigns = getCampaigns($con);
/*$cities = getCities($con);
$categories = getCategories($con);
$counties = getCounties($con);
$yesterday = date('Y-m-d',strtotime("-1 days"));
*/
//$registrations = getUserList($con, $admin, $user_id);
/*
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
*/
$main_admin = ($_SESSION['rights'] == '1') ? true : false;
$users = ($main_admin) ? getUsers($con) : null;



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

function getOfferList($con) {
    $sql="
        SELECT CONCAT(f.name,' - ', o.title) as 'offer', qe.offer_id
        FROM query_employees qe 
        INNER JOIN offer o ON o.id = qe.offer_id
        INNER JOIN firm f ON f.id = o.firm_id
        GROUP BY qe.offer_id";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
}

function getCampaigns($con) {
    $returnArray = [];

    $sql="
        SELECT *
        FROM salesman_campaigns ";

        if($_SESSION['rights'] != '1') {
            $sql = $sql."WHERE user_id=".$_SESSION['query_user_id'];
        }
     
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($result as $campaign) {
            $returnArray[$campaign['id']] = $campaign['name'];
        }
          
        return $returnArray;
}

function getUsers($con) {
    $returnArray = [];

    $sql="
        SELECT *
        FROM query_users WHERE id NOT IN (1, 11)";
     
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($result as $user) {
            $returnArray[$user['id']] = $user['full_name'].' ('.$user['name'].')';
        }
          
        return $returnArray;
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
<title>Megrendelők listája - mumi.hu</title>
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
              <li style="margin-right: 5px;"><a href="index.php">Rögzítés</a></li>
              <li><a href="candidate.php"  >Jelentkezők</a></li>
              <li><a href="salesman_registrations.php"  class="selected">Kapcsolatfelvétel</a></li>
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
        <span class="sr-only">Betöltés...</span>
    </div>
</div>
<div class="form-container container-fluid d-none">

<div class="row w-100" style="font-size: 12px;">
  <form class="w-100" id="idForm" method= "POST">
    <div class="container-fluid mt-2 border p-3 bg-light">
         <div class="row">
            <div class="<?php if($main_admin) { echo "col-md-4"; } else { echo "col-md-6"; } ?> h-100 form-group">
              <label><span style="font-weight:bold;text-transform:uppercase;">Kapmány kiválasztása:</span></label>
                <select class="form-select" id="select-campaigns" name="campaign_id[]" multiple="multiple" placeholder="Válassz egy kampányt...">
                     <option value="" style="padding:10px 5px;">Válassz egy kampányt...</option>
                <?php 
                foreach ($campaigns as $key => $value) {
                    ?>
                    <option value="<?php echo $key ?>"><?php echo $value; ?></option>
                 <?php 
                }
                ?>
              </select>
            </div>
            <div class="<?php if($main_admin) { echo "col-md-4"; } else { echo "col-md-6"; } ?> h-100 form-group">
              <label><span style="font-weight:bold;text-transform:uppercase;">Státusz:</span></label>
              <select class="form-control" id="status" name="status" placeholder="Státusz ...">
                     <option value="" style="padding:10px 5px;">Mindegy</option>
                     <?php foreach($salesmanStatusArray as $key => $value) {?>
                     <option value="<?= $key?>"><?= $value?></option>
                     <?php } ?>
              </select>
            </div>
            <?php 
                if($main_admin) {
                ?>
                    <div class="col-md-4 h-100 form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Értékesítő kiválasztása:</span></label>
                        <select class="form-select" id="select-users" name="user_id[]" multiple="multiple" placeholder="Válassz egy értékesítőket...">
                             <option value="" style="padding:10px 5px;">Válassz egy értékesítőket...</option>
                        <?php 
                        foreach ($users as $key => $value) {
                            ?>
                            <option value="<?= $key?>"><?= $value?></option>
                         <?php 
                        }
                        ?>
                      </select>
                    </div>
                <?php 
                }
                ?>
        </div>
        <div class="collapse" id="collapseExample">
            <div class="row">
                <div class="col-md-4 form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Név:</span></label>
                        <input class="form-control" id="name" name="name" placeholder="Névre való keresés">
                </div>
                <div class="col-md-4 form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">E-mail:</span></label>
                        <input class="form-control" id="email" name="email" placeholder="E-mailre való keresés">
                </div>
                <div class="col-md-4 form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Telefonszám:</span></label>
                        <input  class="form-control" id="telephone" name="telephone" placeholder="Telefonszámra való keresés">
                </div>
                
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                          <label><span style="font-weight:bold;text-transform:uppercase;">Kapcsolatfelvétel dátum nagyobb:</span></label>
                            <input class="form-control" type="date" id="created_at_over" name="created_at_over" placeholder="Regisztráció dátuma nagyobb mint..." pattern="\d{4}-\d{2}-\d{2}">
                        </div>
                <div class="col-md-6 form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Kapcsolatfelvétel dátum kisebb:</span></label>
                        <input class="form-control" type="date" id="created_at_down" name="created_at_down" placeholder="Regisztráció dátuma kisebb mint..." pattern="\d{4}-\d{2}-\d{2}">
                </div>
            </div>
            <div class="row equal-height-row ">
                
            </div>
            <div class="row">
                
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
            </div>
            <div class="col m-auto">
                <div class="border mw-100 p-2 d-inline-block font-weight-bold bg-white" style="font-weight: normal !important;text-transform:uppercase;">
                    Összes kapcsolatfelvétel: <span id="count2" style="font-weight:bold;"></span>
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
        <div style="margin-left:25px;font-weight:bold;"><span id="count"></span> db kapcsolatfelvétel</div>
      </div>
  </div>
</div>
    <div class="row table-container">
    <div class="table-responsive">
        <table id="table" class="table table-striped table-bordered compact stripe" style="font-size:12px;"></table>
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
               $('#select-campaigns').selectize({
                  sortField: 'text'
              });
               $('#select-counties').selectize({
                  sortField: 'text'
              });
               $('#select-users').selectize({
                  sortField: 'text'
              });
               $('#select-driving_license').selectize({
                  sortField: 'text'
              });
               $('#select-cv').selectize({
                  sortField: 'text'
              });

               $('#select-offer').selectize({
                  sortField: 'text'
              });
            
            
           $('#table').dataTable( {
                pageLength: 25,
                destroy: true,
                responsive: true,
                rowReorder: {
                    selector: 'td:nth-child(2)'
                },
                 ajax: {
                    url: 'salesman_process.php?m=q&admin=<?= $admin ?>&user_id=<?= $_SESSION['query_user_id'] ?>' ,
                    dataSrc: 'records',
                    recordsTotal: 'count',
                },
                "columns": [
                    { "data": "id", title: "ID", sortable: true },
                    { "data": "status", title: "Státusz", sortable: false},
                    { "data": "name", title: "Név", sortable: true},
                    { "data": "email", title: "E-mail", sortable: true },
                    { "data": "telephone" , title: "Telefonszám", sortable: true },
                    { "data": "firm_name" , title: "Cégnév", sortable: true },
                    { "data": "positions" , title: "Keresett pozíciók", sortable: true },
                    { "data": "user" , title: "Értékesítő", sortable: true },
                    { "data": "campaign" , title: "Kampány", sortable: true },
                    { "data": "created_at" , title: "Kérelem dátuma", sortable: true },
                    //{ "data": "settings" , title: "Műveletek", sortable: false }
                ],
                columnDefs: [
                    { className: 'dt-center', targets: '_all' }
                ],
                "language": {
                    "lengthMenu": "Megjeleníthető elemek száma oldalanként: _MENU_",
                    "zeroRecords": "Nincs találat",
                    "info": "_PAGE_. oldal a(z) _PAGES_-ből",
                    "infoEmpty": "Nincs egyetlen kapcsolatfelvétel-sem.",
                    "emptyTable": "Nincs egyetlen kapcsolatfelvétel-sem.",
                    "infoFiltered": "(szűrt elemek _MAX_ )",
                    "search": 'Keresés'
                },
                fixedHeader: true,
                initComplete: function() {
                $('#count2').text( this.api().data().length )
                },
                order: [[0, 'desc']]
            }); 

    $("#submit-button").click(function(e){
      $("#idForm").submit(function(e) {
          $('#submit-button').prop('disabled', true);
            $('#submit-button-2').prop('disabled', true);
            e.preventDefault(); // avoid to execute the actual submit of the form.
            cursor_wait();
            var form = $(this);
            var actionUrl = form.attr('action');
        
            $.ajax({
                url: "salesman_process.php?m=q",
                type: "POST",
                data: form.serialize(), 
                 // serializes the form's elements.
                dataType:"JSON",
            }).done( function(data) {
                remove_cursor_wait();
                var table = new $('#table').dataTable( {
                    pageLength: 25,
                    destroy: true,
                    responsive: true,
                    rowReorder: {
                        selector: 'td:nth-child(2)'
                    },
                    "aaData": data.records,
                    "columns": [
                        { "data": "id", title: "ID", sortable: true },
                        { "data": "status", title: "Státusz", sortable: false},
                        { "data": "name", title: "Név", sortable: true},
                        { "data": "email", title: "E-mail", sortable: true },
                        { "data": "telephone" , title: "Telefonszám", sortable: true },
                        { "data": "firm_name" , title: "Cégnév", sortable: true },
                        { "data": "positions" , title: "Keresett pozíciók", sortable: true },
                        { "data": "user" , title: "Értékesítő", sortable: true },
                        { "data": "campaign" , title: "Kampány", sortable: true },
                        { "data": "created_at" , title: "Kérelem dátuma", sortable: true },
                        //{ "data": "settings" , title: "Műveletek", sortable: false }
                    ],
                    columnDefs: [
                        { className: 'dt-center', targets: '_all' }
                    ],
                    "language": {
                        "lengthMenu": "Megjeleníthető elemek száma oldalanként: _MENU_",
                        "zeroRecords": "Nincs találat",
                        "info": "_PAGE_. oldal a(z) _PAGES_-ből",
                        "infoEmpty": "Nincs egyetlen kapcsolatfelvétel-sem.",
                        "emptyTable": "Nincs egyetlen kapcsolatfelvétel-sem.",
                        "infoFiltered": "(szűrt elemek _MAX_ )",
                        "search": 'keresés'
                    },
                    fixedHeader: true,
                    initComplete: function() {
                        $('#count2').text( this.api().data().length )
                    },
                    order: [[0, 'desc']]
                });
                /*
                $([document.documentElement, document.body]).animate({
                    scrollTop: $("#table").offset().top
                }, 1000); */
                $('#submit-button').prop('disabled', false);
                $('#submit-button-2').prop('disabled', false);
                $("#result").hide();

            });
         });
    });

    $("#submit-button-2").click(function(e){
        $("#idForm").submit(function(e) {
            
            e.preventDefault();
            var form = $(this);
            $.ajax({
                type: "POST",
                url: "salesman_process.php",
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
            url: "functions.php?action=status-change-salesman",
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
