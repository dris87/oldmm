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
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

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
              <li style="margin-right: 5px;"><a href="index.php">Rögzítés</a></li>
              <li><a href="candidate.php">Jelentkezők</a></li>
              <li><a href="candidate.php" class="selected">Statisztika</a></li>
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
    <div class="container-fluid mt-2 border p-3 bg-light">
         <div class="row">
            <div id="table_div"></div>
        </div>
        
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


     google.charts.load('current', {'packages':['table']});
      google.charts.setOnLoadCallback(drawTable);

      function drawTable() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Name');
        data.addColumn('number', 'Salary');
        data.addColumn('boolean', 'Full Time Employee');
        data.addRows([
          ['Mike',  {v: 10000, f: '$10,000'}, true],
          ['Jim',   {v:8000,   f: '$8,000'},  false],
          ['Alice', {v: 12500, f: '$12,500'}, true],
          ['Bob',   {v: 7000,  f: '$7,000'},  true]
        ]);

        var table = new google.visualization.Table(document.getElementById('table_div'));

        table.draw(data, {showRowNumber: true, width: '100%', height: '100%'});
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
