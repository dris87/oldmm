<?php
session_start();
if(!$_SESSION['query_user']) {
    include 'login.php';
    return;
}
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

?>

<html>
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js" integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css" integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"> 
<title>Munkavállaló szűrés - mumi.hu</title>
</head>
<body style="font-family: 'Barlow', sans-serif;margin:20px;">
    
    <br><br>
    <div style="display:flex;">
        <div  style="width: 50%">
            <img src="https://mumi.hu/images/mumi-email_logo.png" />
        </div>
        <div style="width: 50%; text-align:right;">
            <form id="offerForm" action="offer.php" method= "POST">
             <input type="date" id="date" name="date" value="<?= $yesterday ?>" />   
            <button id="offersButton" style="font-family: 'Barlow', sans-serif;text-transform: uppercase;background-color:orange;color:#ffffff;font-weight:bold;padding: 10px;border:none;cursor:pointer;">Lejárt hírdetések lekérdezés</button>
            </form>
            <button id="logout" style="font-family: 'Barlow', sans-serif;text-transform: uppercase;background-color:red;color:#ffffff;font-weight:bold;padding: 10px;border:none;cursor:pointer;">Kijelentkezés</button>
        </div>
    </div>
  <form id="idForm" action="process.php" method= "POST">
<div style="float:left;width:50%;">
    <div style="padding-left: 65px;">
      <label><p style="padding-bottom: 5px;"><span style="font-weight:bold;text-transform:uppercase;">Megye:</span></p></label>
        <select id="select-counties" name="counties[]" multiple="multiple" placeholder="Válassz egy megyét...">
             <option value="" style="padding:10px 5px;">Válassz egy megyét...</option>
        <?php 
        foreach ($counties as $county) {
            ?>
            <option value="<?php echo $county['id']; ?>"><?php echo $county['value']; ?></option>
         <?php 
        }
        ?>
      </select>
   
    </div>
</div>
<div style="float:right;width:50%;">
 
    <div style="padding-left: 65px;">
      <label><p style="padding-bottom: 5px;"><span style="font-weight:bold;text-transform:uppercase;">Város:</span></p></label>
        <select id="select-state" name="states[]" multiple="multiple" placeholder="Válassz egy települést...">
             <option value="" style="padding:10px 5px;">Válassz egy települést...</option>
        <?php 
        foreach ($cities as $city) {
            ?>
            <option value="<?php echo $city['id']; ?>"><?php echo $city['value']; ?></option>
         <?php 
        }
        ?>
      </select>
   
    </div>
</div>
    
    <div style="clear:both;padding-top:20px;padding-left: 65px;">

      <label><p style="padding-bottom: 5px;"><span style="font-weight:bold;text-transform:uppercase;">Kategória:</span></p></label>
        <select id="select-categories" name="categories[]" multiple="multiple" placeholder="Válassz egy kategóriát...">
             <option value="" style="padding:10px 5px;">Válassz egy kategóirát...</option>
        <?php 
        foreach ($categories as $category) {
            ?>
            <option value="<?php echo $category['id']; ?>"><?php echo $category['value']; ?></option>
         <?php 
        }
        ?>
      </select>
   
    </div>
    <div style="margin-left:62px;width:50%; display:flex;">
                <div style="width:50%;">
                          
                    <label><p style="padding-bottom: 5px;"><span style="font-weight:bold;text-transform:uppercase;">Létrehozás dátuma nagyobb:</span></p></label>
                            <input class="form-control" type="date" id="created_over" name="created_over" placeholder="Létrehozás dátum nagyobb mint..." pattern="\d{4}-\d{2}-\d{2}">
                        </div>
                <div style="width:50%">
                      <label><p style="padding-bottom: 5px;"><span style="font-weight:bold;text-transform:uppercase;">Létrehozás dátuma kissebb:</span></p></label>
                        <input class="form-control" type="date" id="created_down" name="created_down" placeholder="Létrehozás dátum kissebb mint..." pattern="\d{4}-\d{2}-\d{2}">
                </div>
            </div>
    <br><br>
    <div style="padding-left: 65px;"><button id="submit-button" style="font-family: 'Barlow', sans-serif;text-transform: uppercase;background-color: #F79F39;color:#ffffff;font-weight:bold;padding: 10px;border:none;cursor:pointer;">Lekérdezés</button></div>
  </form>
  <div id="result" style="display:none;">
      <div  style="margin-left:65px;display:none;padding:20px;background-color:#1C75BC; color:#ffffff;display:flex !important; ">
        <div><a id="link" href="" target="_blank" style="max-width: 500px;color:#ffffff;text-decoration:none;"> </a></div>
        <div style="margin-left:25px;font-weight:bold;"><span id="count"></span> db önéletrajz</div>
      </div>
  </div>
  <script>

        $(document).ready(function () {
              $('#select-state').selectize({
                  sortField: 'text'
              });
               $('#select-categories').selectize({
                  sortField: 'text'
              });
               $('#select-counties').selectize({
                  sortField: 'text'
              });
          });

    $("#idForm").submit(function(e) {
        $('#submit-button').prop('disabled', true);
        e.preventDefault(); // avoid to execute the actual submit of the form.

        var form = $(this);
        var actionUrl = form.attr('action');
        
        $.ajax({
            type: "POST",
            url: actionUrl,
            data: form.serialize(), // serializes the form's elements.
            dataType:"JSON",
            success: function(data)
            {
              $("#link").attr("href", data.link);
              $("#link").html(data.link);
              $("#count").html(data.count);
              $("#result").show();
              $('#submit-button').prop('disabled', false);
            }
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
                    window.location.href = "https://mumi.hu/query/index.php";
                }
                
            }
        });
        
    });

   $("#offerForm").submit(function(e) {
        $('#offersButton').prop('disabled', true);
        e.preventDefault(); // avoid to execute the actual submit of the form.
        var form = $(this);
        var actionUrl = form.attr('action');

        $.ajax({
            type: "POST",
            url: actionUrl,
            data: form.serialize(), 
             // serializes the form's elements.
            dataType:"JSON",
            success: function(data)
            {

                $("#link").attr("href", data.link);
                $("#link").html(data.link);
                $("#count").html(data.count);
                $("#result").show();
                $('#offersButton').prop('disabled', false);
                
            }
        });
        
    });
  </script>
</body>
</html>
