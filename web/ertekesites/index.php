<?php
session_set_cookie_params('86400');
ini_set("session.gc_maxlifetime", 86400);
ini_set("session.cookie_lifetime", 86400);
session_start([
    'cookie_lifetime' => 86400,
]);
if(!$_GET['campaign']) {
    include 'login.php';
    return;
}
$campaign_slug = $_GET['campaign'];

ini_set('memory_limit', '-1');
ini_set('date.timezone', 'Europe/Budapest');
set_time_limit(0);
error_reporting(0);
ini_set('display_errors', 1);


$con = connectDB('localhost', 'c1_web', 'c1_web', '5DpzFiY@5');

$findCampaign = getCampaign($con, $campaign_slug);
$user = getUser($con, $findCampaign['user_id']);

if(!isset($findCampaign['status']) || $findCampaign['status'] != 1) {
header("Location: https://mumi.hu");
exit;
}


//$cities = getCities($con);
//$categories = getCategories($con);
//$counties = getCounties($con);
//$driving_licenses = getDrivingLicences($con);
//$languagesArray = getLanguages($con);
//$languages = [];

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

function getCampaign($con, $slug) {

    $sql="
        SELECT c.*
        FROM salesman_campaigns c
        WHERE c.slug='$slug'";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
     
        return $result;
}

function getUser($con, $user_id) {
    $sql="
        SELECT *
        FROM query_users
        WHERE id=$user_id";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
}

function getOfferProp($con, $offer_id) {
    $resultBack = "";
    $sql="
        SELECT *
        FROM offer_dictionary_relation
        WHERE offer_id=$offer_id";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($result as $prop) {

        $sql="
        SELECT value
        FROM dictionary
        WHERE id=".$prop['dictionary_id']." AND dictionary_type=9";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $propResult = $stmt->fetch(PDO::FETCH_ASSOC);
        if(isset($propResult['value'])) {
            $resultBack = $propResult['value'];
        }
    }

    return $resultBack;
}


?>
<!DOCTYPE html>
<html>
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js" integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css" integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous"> 
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<title>Megrendelés - Munkalehetőség Mindenkinek Kft.</title>
<style>
input:not([type=checkbox]) {
    border: 1px solid #ccc;
    padding: 6px 12px;
    display: inline-block;
    width: 100%;
    overflow: hidden;
    position: relative;
    z-index: 1;
    font-size: 12px;
}
.menu {
    ul {
      list-style-type: none;
      overflow: hidden;
      margin: 0 0 0 15px;
      padding: 0;
      display: flex;
      align-items: center;
    }

    li {
      float: left;
    }

    li a {
      display: block;
      color: #FBA500;
      text-align: center;
      padding: 12px;
      text-decoration: none;
      font-weight: bold;
      border-radius: 5px;
      text-transform: uppercase;
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


/* Updated CSS with open selector */

.modal.open {
  transform: translateX(0px);
}

.modal {
  /* Update CSS with transition and transform rules */
  transition: transform 1s linear;
  transform: translateX(-100%);
  position: fixed;
  z-index: 1;
  padding-top: 100px;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(255, 255, 255, 0.8);
}

.modal-content {
  margin: auto;
  padding: 20px;
  width: 80%;
}

.close {
  color: #323232;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: #424242;
  text-decoration: none;
  cursor: pointer;
}


  .checkbox-wrapper-28 {
    --size: 25px;
    position: relative;
  }

  .checkbox-wrapper-28 *,
  .checkbox-wrapper-28 *:before,
  .checkbox-wrapper-28 *:after {
    box-sizing: border-box;
  }

  .checkbox-wrapper-28 .promoted-input-checkbox {
    border: 0;
    clip: rect(0 0 0 0);
    height: 1px;
    margin: -1px;
    overflow: hidden;
    padding: 0;
    position: absolute;
    width: 1px;
  }

  .checkbox-wrapper-28 input:checked ~ svg {
    height: calc(var(--size) * 0.6);
    -webkit-animation: draw-checkbox-28 ease-in-out 0.2s forwards;
            animation: draw-checkbox-28 ease-in-out 0.2s forwards;
  }
  .checkbox-wrapper-28 label:active::after {
    background-color: #e6e6e6;
  }
  .checkbox-wrapper-28 label {
    color: #0080d3;
    line-height: var(--size);
    cursor: pointer;
    position: relative;
  }
  .checkbox-wrapper-28 label:after {
    content: "";
    height: var(--size);
    width: var(--size);
    margin-right: 8px;
    float: left;
    border: 2px solid #0080d3;
    border-radius: 3px;
    transition: 0.15s all ease-out;
  }
  .checkbox-wrapper-28 svg {
    stroke: #0080d3;
    stroke-width: 3px;
    height: 0;
    width: calc(var(--size) * 0.6);
    position: absolute;
    left: calc(var(--size) * 0.21);
    top: calc(var(--size) * 0.2);
    stroke-dasharray: 33;
  }

  @-webkit-keyframes draw-checkbox-28 {
    0% {
      stroke-dashoffset: 33;
    }
    100% {
      stroke-dashoffset: 0;
    }
  }

  @keyframes draw-checkbox-28 {
    0% {
      stroke-dashoffset: 33;
    }
    100% {
      stroke-dashoffset: 0;
    }
  }


    
</style>
<script src="https://www.google.com/recaptcha/enterprise.js?render=6LeF2P0qAAAAAAgR-JnzqscuAfdVH8PFF_6hP5_V"></script>

</head>
<body style="font-family: 'Barlow', sans-serif;margin:20px;font-size: 12px;">
    <div class="row">
            <img class="center-block mx-auto mb-3 " src="https://mumi.hu/images/mumi-email_logo.png" />
    </div>

<div class="container-fulid">
	<div id="info-text" class="container-fulid mt-2 border p-3 bg-light" style="background-color:#fbe5ce !important;border-color:#F79F39 !important;">
		<p style="font-size: 22px;font-weight: bold;text-transform:uppercase;">Megrendelés munkaadók részére</p>
		Üdvözlöm! Köszönöm, hogy érdeklődik szolgáltatásunk iránt, egy lépéssel közelebb került ahhoz, hogy megismerje, hogyan segíthetek Önnek releváns jelölteket biztosítani nyitott pozícióira.<br>
		Kérem, töltse ki az alábbi űrlapot és kattintson a "MEGRENDELEM" gombra. Ezzel rögzítem a megrendelését, és hamarosan visszahívom Önt, hogy személyre szabottan válaszoljak kérdéseire és bemutassam, milyen előnyökhöz juthat hozzá a szolgáltatásaink révén.
	</div>
</div>
     
<div class="container-fulid mt-2 border p-3 bg-light">
            <div id="success" class="row w-75 mx-auto d-none" style="font-size:18px;">
                <div class="col alert alert-success text-center w-">
                  <span class="font-weight-bold" style="font-size:30px"><i class="fa fa-check"></i></span><br><br>
				  <strong>Köszönöm!</strong><br><br>
				  Kapcsolatfelvételi kérelmét megkaptam, hamarosan keresni fogom Önt a megadott elérhetőségek egyikén, hogy személyre szabott megoldásokat kínáljak nyitott pozícióinak betöltésére.
                  <br>
                  <br>
                  <p>
                    Üdvözlettel,<br>
                    <b><?php echo $user['full_name']; ?></b><br>
					<i>munkaerőpiaci tanácsadó</i><br>
                    <?php echo $user['email']; ?><br>
                    <?php echo $user['telephone']; ?><br><br>
					Munkalehetőség Mindenkinek Kft.
                  </p>
                </div>
            </div>
                
            <form class="form-horizontal" id="idForm" action="saveSalesman.php" method= "POST">
                <input type="hidden" id="campaign_id" name="campaign_id" required value="<?= $findCampaign['id'] ?>">
                <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
                <div class="form-group">
                        <label class="control-label"><span style="font-weight:bold;text-transform:uppercase;">Név:</span></label>
                        <input class="form-control" id="name" type="text" name="name" required value="" minlength="3">
                </div>
                <div class="form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Telefonszám:</span></label>
                        <input class="form-control" type="text" id="telephone" name="telephone" required value="">
                        <div id="telephone-error" class="alert alert-danger d-none">
                        Már jelentkeztek ezzel a telefonszámmal!
                     </div>
                </div>
                <div class="form-group">
                  <label class="control-label"><span style="font-weight:bold;text-transform:uppercase;">E-mail cím:</span></label>
                    <input class="form-control" id="email" type="email" name="email" value="" >
                    <div id="email-error" class="alert alert-danger d-none">
                        Már jelentkeztek ezzel az e-mail címmel!
                     </div>
                </div>
                <div class="form-group">
                        <label class="control-label"><span style="font-weight:bold;text-transform:uppercase;">Cégnév:</span></label><br><i>Kérjük, adja meg, hogy melyik cég képviseletében kíván kapcsolatba lépni velem.</i><br>
                        <input class="form-control" id="firm_name" type="text" name="firm_name" required value="" minlength="3">
                </div>
                
                <div class="form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Nyitott pozíció(k): </span></label><br><i>Kérjük, adja meg, hogy milyen nyitott pozíciókat szeretne betölteni munkavállalókkal.</i><br>
                       <textarea class="form-control" id="categories" name="categories" row="3" maxlength="255"></textarea>
                </div>
				<br>
                <div id="error" class="alert alert-danger d-none">
                </div>
                <br>
                <div class="form-group">
                    <button id="submit-button" style="font-family: 'Barlow', sans-serif;text-transform: uppercase;background-color: #1C75BC;color:#ffffff;font-weight:bold;border-radius: 5px;padding: 10px;border:none;cursor:pointer;">MEGRENDELEM</button>
                    <div id="spinner" class="spinner-border text-warning d-none" role="status">
                      <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </form>
            <!-- Button trigger modal -->
</div>


  <script>

        $("#name").on('keyup', function(e) {
            const regEx1 = /[^A-Za-z áéúőóíüöűÁÉÚŐÓÜŰÍ.\-]$/;
            $(this).val($(this).val().replace(regEx1, ''));
        });

    $("#idForm").submit(function(e) {
    e.preventDefault();
    $('#submit-button').prop('disabled', true);
    $("#spinner").removeClass('d-none'); 
    $("#error").addClass('d-none'); 
    $("#error").html('');

    const currentForm = this; // Eltároljuk a form referenciát

    grecaptcha.enterprise.ready(function() {
        grecaptcha.enterprise.execute('6LeF2P0qAAAAAAgR-JnzqscuAfdVH8PFF_6hP5_V', {action: 'submit'}).then(function(token) {
            // Token hozzáadása
            $("#g-recaptcha-response").val(token);
            
            var formData = new FormData(currentForm); // Az eltárolt referenciát használjuk
            
            $.ajax({
                type: "POST",
                url: $(currentForm).attr('action'),
                data: formData,
                dataType: 'JSON',
                contentType: false,
                cache: false,
                processData: false,
                success: function(data) {
                    if(data === "ok") {
                        $("#success").removeClass('d-none');
                        $("#idForm").addClass('d-none');
                        $("#spinner").addClass('d-none');
                        $("#info-text").addClass('d-none');  
                    } else {
                        $("#spinner").addClass('d-none');  
                        $("#error").removeClass('d-none');                    
                        $("#error").html(data);
                        $('#submit-button').prop('disabled', false);
                    }
                },
                error: function() {
                    $("#spinner").addClass('d-none');
                    $("#error").removeClass('d-none');
                    $("#error").html("Váratlan hiba történt. Kérjük, próbálja újra később.");
                    $('#submit-button').prop('disabled', false);
                }
            });
        }).catch(function(error) {
            console.error("reCAPTCHA hiba:", error);
            $("#spinner").addClass('d-none');
            $("#error").removeClass('d-none');
            $("#error").html("Biztonsági ellenőrzési hiba történt. Kérjük, frissítse az oldalt és próbálja újra.");
            $('#submit-button').prop('disabled', false);
        });
    });
});
/*
   $('#email').on('change', function(e) {
        $("#email-error").addClass('d-none'); 
        const email = document.getElementById('email');
        const offer_id = document.getElementById('offer_id');
        $.ajax({
            type: "POST",
            url: "functions.php?action=email",
            data: { email: email.value, offer_id: offer_id.value },  // serializes the form's elements.
            //contentType: "application/json; charset=utf-8",
            //dataType:"JSON",
            encode: true,
            success: function(data)
            {
                if(data === "error") {
                    $("#email-error").removeClass('d-none');
                } else {
                    $("#email-error").addClass('d-none');                    
                }
            }
        });
   });
   */
  </script>
</body>
</html>
