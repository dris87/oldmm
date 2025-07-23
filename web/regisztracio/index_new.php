<?php
session_set_cookie_params('86400');
ini_set("session.gc_maxlifetime", 86400);
ini_set("session.cookie_lifetime", 86400);
session_start([
    'cookie_lifetime' => 86400,
]);

// CSRF token generálása, ha még nem létezik
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
$drivings = getDrivingLicences($con);
$languagesArray = getLanguages($con);
$languages = [];
$levels = [
    "alapfok",
    "középfok",
    'felsőfok'
];
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

function getOffer($con, $offer_id) {
    $sql="
        SELECT o.*
        FROM offer o
        WHERE o.id=$offer_id";
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
<title>Jelentkezési űrlap - mumi.hu</title>
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

/* Anti-bot mező elrejtése - többszörös módszerrel */
.important-field {
    opacity: 0;
    position: absolute;
    top: 0;
    left: 0;
    height: 0;
    width: 0;
    z-index: -1;
    overflow: hidden;
    visibility: hidden;
    pointer-events: none;
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
        <p style="font-size: 22px;font-weight: bold;text-transform:uppercase;">Jelentkezési űrlap</p>
        Köszönjük, hogy érdeklődik a mumi állásportál iránt! Az alábbi űrlap kitöltésével jelentkezését rögzítjük adatbázisunkban. Amennyiben egy cég olyan álláshirdetést tesz fel az oldalunkra, amelyre Ön alkalmas lehet, elküldjük az önéletrajzát a cégnek. Ha az önéletrajza megfelel egy álláslehetőség követelményeinek, a cég felveszi Önnel a kapcsolatot.
        <br>
        <b>Kérjük, adatait pontosan adja meg, hogy kollégánk felvehesse Önnel a kapcsolatot az álláshirdetéssel kapcsolatban és segíthesse Önt az elhelyezkedésben.</b>
    </div>
</div>
     
<div class="container-fulid mt-2 border p-3 bg-light">
            <div id="success" class="row w-75 mx-auto d-none" style="font-size:18px;">
                <div class="col alert alert-success text-center w-">
                  <strong>Köszönjük, jelentkezését rögzítettük.</strong>
                  <br>
                  <span class="font-weight-bold" style="font-size:30px"><i class="fa fa-check"></i></span>
                </div>
            </div>
                
            <form class="form-horizontal" id="idForm" action="saveEmployee.php" method= "POST" enctype="multipart/form-data">
                <!-- CSRF védelem token -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="important-field">
                    <input type="text" name="company_website" id="company_website" autocomplete="off" tabindex="-1" placeholder="">
                </div>
                
                <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
                <input type="hidden" id="sources" name="sources" required value="Regisztrációs oldal">
                <div class="form-group">
                        <label class="control-label"><span style="font-weight:bold;text-transform:uppercase;">Név:</span></label>
                        <input class="form-control" id="name" type="text" name="name" required value="">
                </div>
                <div class="form-group">
                    <label><span style="font-weight:bold;text-transform:uppercase;">Telefonszám:</span></label>
                    <input id="telephone" class="form-control" type="text" name="telephone" required value="">
                    <div id="telephone-error" class="alert alert-danger d-none">
                        Már létezik ilyen telefonszám az adatbázisban!
                    </div>
                </div>
                <div class="form-group">
                  <label class="control-label"><span style="font-weight:bold;text-transform:uppercase;">E-mail cím:</span></label>
                    <input class="form-control" id="email" type="email" name="email" value="" >
                    <div id="email-error" class="alert alert-danger d-none">
                        Már létezik ilyen e-mail cím az adatbázisban!
                     </div>
                </div>
                <div class="form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Város:</span></label>
                        <select class="form-select" id="select-state" name="states" placeholder="Válassz egy települést...">
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
                <div class="form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Megye:</span></label>
                        <select class="form-select" id="select-counties"  name="counties" placeholder="Válassz egy megyét...">
                             <option value="">Válassz egy megyét...</option>
                        <?php 
                        foreach ($counties as $county) {
                            ?>
                            <option value="<?php echo $county['value']; ?>"><?php echo $county['value']; ?></option>
                         <?php 
                        }
                        ?>
                      </select>
                </div>
                <div class="form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Keresett pozíció(k):</span></label>
                        <select class="form-select" id="select-categories" required name="categories[]" multiple="multiple" placeholder="Válassz egy keresett pozíciót..." style="border:2px solid #1C75BC !important;" required>
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
                <div class="form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Születési dátum:</span></label>
                        <input class="form-control" type="date" name="birthday" value="" pattern="\d{4}-\d{2}-\d{2}" max="9999-12-31">
                </div>

                <div class="form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Vezetői engedélyek:</span></label>
                        <select class="form-select" id="select-driving_license" name="driving_license[]" multiple="multiple" placeholder="Válassz egy vezetői engedélyt...">
                             <option value="" style="padding:10px 5px;">Válassz egy vezetői...</option>
                        <?php 
                        foreach ($drivings as $driving) {
                            ?>
                            <option value="<?php echo $driving['value']; ?>"><?php echo $driving['value']; ?></option>
                         <?php 
                        }
                        ?>
                      </select>
                </div>
                <div class="form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Nyelvek:</span></label>
                        <select class="form-select" id="select-languages" name="languages[]" multiple="multiple" placeholder="Válassz egy nyevlet...">
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
                <div class="form-group">
                    <div class="row">
                        <div class="col-2 form-check">
                          <label><span style="font-weight:bold;text-transform:uppercase;">Utazna-e:</span></label>
                            <input id="traveling" type="checkbox" name="traveling" value="1">
                        </div>
                        <div class="d-none" id="traveling_km_box">
                            <div class="col-10">
                              <label><span style="font-weight:bold;text-transform:uppercase;">Km megadása:</span></label>
                                <input type="number" name="traveling_km" step="10" data-id="traveling_km" min="10">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-2 form-check">
                          <label><span style="font-weight:bold;text-transform:uppercase;">Külföld:</span></label>
                            <input id="foreign" type="checkbox" name="foreign" value="1">
                        </div>
                        <div class="d-none" id="where_foreign_box">
                            <div class="col-10">
                              <label><span style="font-weight:bold;text-transform:uppercase;">Külföldre hová:</span></label>
                                <textarea name="where_foreign" rows="4" cols="50"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <div class="checkbox-wrapper-28">
                  <input id="tmp-28" type="checkbox" class="promoted-input-checkbox" name="tmp-28" required/>
                  <svg><use xlink:href="#checkmark-28" /></svg>
                  <label for="tmp-28">
                    <span style="color:#212529;">Elfogadom az</span> <a href="https://mumi.hu/docs/MUMI-Altalanos_szerzodesi_feltetelek.pdf" target="_blank">Általános Szerződési Feltételeket</a>.
                  </label>
                  <svg xmlns="http://www.w3.org/2000/svg" style="display: none">
                    <symbol id="checkmark-28" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-miterlimit="10" fill="none"  d="M22.9 3.7l-15.2 16.6-6.6-7.1">
                      </path>
                    </symbol>
                  </svg>
                </div>
                <div class="checkbox-wrapper-28">
                  <input id="tmp-29" type="checkbox" class="promoted-input-checkbox" name="tmp-29" required/>
                  <svg><use xlink:href="#checkmark-28" /></svg>
                  <label for="tmp-29">
                    <span style="color:#212529;">Elfogadom az</span> <a href="https://mumi.hu/docs/MUMI-Adatvedelmi_szabalyzat.pdf" target="_blank">Adatvédelmi szabályzatot</a>.
                  </label>
                  <svg xmlns="http://www.w3.org/2000/svg" style="display: none">
                    <symbol id="checkmark-28" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-miterlimit="10" fill="none"  d="M22.9 3.7l-15.2 16.6-6.6-7.1">
                      </path>
                    </symbol>
                  </svg>
                </div>
                <div id="error" class="alert alert-danger d-none">
                </div>
                <br>
                <div class="form-group">
                    <button id="submit-button" 
                            style="font-family: 'Barlow', sans-serif;text-transform: uppercase;background-color: #1C75BC;color:#ffffff;font-weight:bold;border-radius: 5px;padding: 10px;border:none;cursor:pointer;">
                        Regisztrálok
                    </button>
                    <div id="spinner" class="spinner-border text-warning d-none" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </form>
            <!-- Button trigger modal -->
</div>
<script>

        $(document).ready(function () {
              
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

    // reCAPTCHA végrehajtása
    grecaptcha.enterprise.ready(function() {
        grecaptcha.enterprise.execute('6LeF2P0qAAAAAAgR-JnzqscuAfdVH8PFF_6hP5_V', {action: 'submit'}).then(function(token) {
            // Token hozzáadása a form adatokhoz
            let form = $("#idForm");
            $("#g-recaptcha-response").val(token);
            var formData = new FormData(form[0]);

            $.ajax({
                type: "POST",
                url: form.attr('action'),
                data: formData,
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                success: function(response) {
                    $("#spinner").addClass('d-none');
                    if(response === "ok") {
                        // Form reset
                        form[0].reset();
                        
                        // Selectize mezők resetelése
                        if($('#select-state')[0].selectize) {
                            $('#select-state')[0].selectize.clear();
                        }
                        if($('#select-counties')[0].selectize) {
                            $('#select-counties')[0].selectize.clear();
                        }
                        if($('#select-categories')[0].selectize) {
                            $('#select-categories')[0].selectize.clear();
                        }
                        if($('#select-languages')[0].selectize) {
                            $('#select-languages')[0].selectize.clear();
                        }
                        if($('#select-driving_license')[0].selectize) {
                            $('#select-driving_license')[0].selectize.clear();
                        }
                        
                        // Extra mezők elrejtése
                        $('#traveling_km_box').addClass('d-none');
                        $('#where_foreign_box').addClass('d-none');
                        
                        // Form és info elrejtése
                        $("#idForm").addClass('d-none');
                        $("#info-text").addClass('d-none');
                        
                        // Sikeres üzenet megjelenítése
                        $("#success").removeClass('d-none');
                    } else {
                        $("#error").removeClass('d-none');                    
                        $("#error").html(response);
                        $('#submit-button').prop('disabled', false);
                    }
                },
                error: function() {
                    $("#spinner").addClass('d-none');
                    $("#error").removeClass('d-none');
                    $("#error").html("Hiba történt a küldés során. Kérjük próbálja újra.");
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
        $('#email').on('change', function(e) {
        $("#email-error").addClass('d-none'); 
        const email = document.getElementById('email');
        $.ajax({
            type: "POST",
            url: "functions.php?action=email",
            data: { email: email.value },
            encode: true,
            success: function(data) {
                if(data == '"error"') {
                    $("#email-error").removeClass('d-none');
                    $('#submit-button').prop('disabled', true);
                } else {
                    $("#email-error").addClass('d-none'); 
                    $('#submit-button').prop('disabled', false);                   
                }
            }
        });
    });

   $('#telephone').on('change', function(e) {
    $("#telephone-error").addClass('d-none'); 
    const telephone = document.getElementById('telephone');
    $.ajax({
        type: "POST",
        url: "functions.php?action=telephone",
        data: { telephone: telephone.value },
        encode: true,
        success: function(data) {
            if(data == '"error"') {
                $("#telephone-error").removeClass('d-none');
                $('#submit-button').prop('disabled', true);
            } else {
                $("#telephone-error").addClass('d-none'); 
                $('#submit-button').prop('disabled', false);                   
            }
        }
    });
});
   if($("#traveling").is(':checked')) {
      $("#traveling_km_box").removeClass('d-none');
   } else {
     $("#traveling_km_box").addClass('d-none');
   }

   if($("#foreign").is(':checked')) {
      $("#where_foreign_box").removeClass('d-none');
   } else {
     $("#where_foreign_box").addClass('d-none');
   }

   $("#traveling").on('click', function(e) {
       if($(this).is(':checked')) {
          $('#traveling_km').prop('required',false);
          $("#traveling_km_box").removeClass('d-none');
       } else {
        $('#traveling_km').prop('required',false);
         $("#traveling_km_box").addClass('d-none');
       }
    });

   $("#foreign").on('click', function(e) {
       if($(this).is(':checked')) {
          $("#where_foreign_box").removeClass('d-none');
       } else {
         $("#where_foreign_box").addClass('d-none');
       }
    });
  </script>
</body>
</html>