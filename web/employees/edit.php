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

$id=(int) $_GET['e'];

if(!$id || !is_int($id)) {
    header('Location: index.php');
    exit();
}


$employee = getEmployee($con, $_GET['e']);

if(!$employee) {
    header('Location: index.php');
    exit();
}

$cities = getCities($con);
$categories = getCategories($con);
$counties = getCounties($con);
$yesterday = date('Y-m-d',strtotime("-1 days"));

$drivings = getDrivingLicences($con);
$languagesArray = getLanguages($con);
$languages = [];

if(!empty($employee['offer_id'])) {
    //die($employee['offer_id']);
    $offer = getOffer($con, $employee['offer_id']);

    $firm = (isset($offer['firm_id'])) ? getFirm($con, $offer['firm_id']) : null;

}
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

function getEmployee($con, $id) {

    $sql="SELECT *
      FROM query_employees e
      WHERE e.id=".$id;

    $stmt = $con->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
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
        //die($result);
        return $result;
}

function getFirm($con, $firm_id) {
    $sql="
        SELECT *
        FROM firm
        WHERE id=$firm_id";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous"> 
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<title>Módosítás - mumi.hu</title>
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
                  <li><a href="candidate.php" class="selected">Jelentkezők</a></li>
                  <li><a href="salesman_registrations.php">Kapcsolatfelvétel</a></li>
                  <li><a href="firm.php">Cégkereső</a></li>
                </ul>
            </div>
        </div>
        <div class="col-4" style="text-align:right;top: 10px;">
            <button id="logout" style="font-family: 'Barlow', sans-serif;text-transform: uppercase;background-color:#efefef;font-weight:bold;padding: 10px;border:none;cursor:pointer;">Kijelentkezés</button>
        </div>
    </div>
     
<div class="container-fulid mt-2 border p-3 bg-light">
                <?php 

                            if(!empty($employee['offer_id'])){
                ?>  
                <div class="row">
                
                        <div class="center-block mx-auto">
                            <spam style="font-weight: bold; font-size: 16px; color: red;">
                             DIREKT JELENTKEZÉS
                            </spam>
                        </div>
                        
                        <?php 

                            if(isset($offer) && isset($firm) ) {
                        ?>  
                        <div class="center-block mx-auto">
                            <p style="font-size: 16px;"> 
                            <a href="https://mumi.hu/hu/allas/<?= $offer['slug'] ?>" target="_blank" title="Hírdetés"><?= $firm['name']?> - <?= $offer['title'] ?></a>
                            </p>
                        </div>
                        <?php   
                            } else {
                                
                            ?>
                            <div class="center-block mx-auto">
                                <p style="font-size: 18px; color:orange; font-weight: bold;"> 
                                    TÖRÖLT HÍRDETÉS!
                                </p>
                             </div>
                        <?php 
                            }
                        ?>
                </div>
            <?php   
                }
            ?>
            <form class="form-horizontal" id="idForm" action="updateEmployee.php" method= "POST">
                <input type="hidden" name="id" required value="<?php echo $employee['id']; ?>">
                <div class="form-group">
                  <label><span style="font-weight:bold;text-transform:uppercase;">Státusz:</span></label>
                  <select class="form-control" id="status" name="status" placeholder="Státusz ...">
                         <?php foreach($statusArray as $key => $value) {?>
                         <option value="<?= $key?>" <?php if($employee['status'] == $key) echo "selected"; ?>><?= $value?></option>
                         <?php } ?>
                  </select>
                </div>
                <div class="form-group">
                        <label class="control-label"><span style="font-weight:bold;text-transform:uppercase;">Név:</span></label>
                        <input class="form-control" type="text" name="name" required value="<?php echo $employee['name']; ?>">
                </div>
                <div class="form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Telefonszám:</span></label>
                        <input class="form-control" type="text" name="telephone" required value="<?php echo $employee['telephone']; ?>">
                </div>
                <div class="form-group">
                    <label class="control-label"><span style="font-weight:bold;text-transform:uppercase;">E-mail cím:</span></label>
                    <input class="form-control" type="email" name="email" value="<?php echo $employee['email']; ?>" > 
                </div>
                <div class="form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Város:</span></label>
                        <select class="form-select" id="select-state" name="states" placeholder="Válassz egy települést...">
                             <option value="" style="padding:10px 5px;">Válassz egy települést...</option>
                        <?php 
                        foreach ($cities as $city) {
                            ?>
                            <option value="<?php echo $city['value']; ?>" <?php if($employee['city'] === $city['value']) { echo "selected"; } ?>><?php echo $city['value']; ?></option>
                         <?php 
                        }
                        ?>
                      </select>
                </div>
                <div class="form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Megye:</span></label>
                        <select class="form-select" id="select-counties" name="counties" placeholder="Válassz egy megyét...">
                             <option value="">Válassz egy megyét...</option>
                        <?php 
                        foreach ($counties as $county) {
                            ?>
                            <option value="<?php echo $county['value']; ?>" <?php if($employee['county'] === $county['value']) { echo "selected"; } ?>><?php echo $county['value']; ?></option>
                         <?php 
                        }
                        ?>
                      </select>
                </div>
                 <div class="form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Forrás:</span></label>
                        <select class="form-select" id="select-sources"  name="sources" placeholder="Válassz egy forrást...">
                             <option value="">Válassz egy forrást...</option>
                        <?php 
                        foreach ($sources as $source) {
                        ?>
                            <option value="<?php echo $source; ?>" <?php if($employee['source'] === $source) { echo "selected"; } ?>><?php echo $source; ?></option>
                        <?php 
                        }
                        ?>
                      </select>
                </div>
                <div class="form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Kategória:</span></label>
                        <select class="form-select" id="select-categories" name="categories[]" multiple="multiple" required placeholder="Válassz egy kategóriát...">
                             <option value="" style="padding:10px 5px;">Válassz egy kategóirát...</option>
                        <?php
                        $categoriesArray = explode(',', $employee['positions']); 
                        foreach ($categories as $category) {
                            ?>
                            <option value="<?php echo $category['value']; ?>" <?php if(in_array($category['value'], $categoriesArray)) { echo "selected"; } ?>><?php echo $category['value']; ?></option>
                         <?php 
                        }
                        ?>
                      </select>
                </div>

            
                <div class="form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Születési dátum:</span></label>
                        <input class="form-control" type="date" name="birthday" value="<?php echo $employee['birthday']; ?>" pattern="\d{4}-\d{2}-\d{2}">
                </div>
                

                <div class="form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">Vezetői engedélyek:</span></label>
                        <select class="form-select" id="select-driving_license" name="driving_license[]" multiple="multiple" placeholder="Válassz egy vezetői engedélyt...">
                             <option value="" style="padding:10px 5px;">Válassz egy vezetői...</option>
                        <?php 
                        $drivingArray = explode(',', $employee['driving_license']);
                        foreach ($drivings as $driving) {
                            ?>
                            <option value="<?php echo $driving['value']; ?>" <?php if(in_array($driving['value'], $drivingArray)) { echo "selected"; } ?>><?php echo $driving['value']; ?></option>
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
                        $languageArray = explode(',', $employee['language']); 
                        foreach ($languages as $language) {
                            
                            ?>
                            <option value="<?php echo $language['value']; ?>" <?php if(in_array($language['value'], $languageArray)) { echo "selected"; } ?>><?php echo $language['value']; ?></option>
                         <?php 
                        }
                        ?>
                      </select>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-2 form-check">
                          <label><span style="font-weight:bold;text-transform:uppercase;">Utazna-e:</span></label>
                            <input id="traveling" type="checkbox" name="traveling" value="1" <?php if($employee['traveling'] === '1') { echo "checked"; } ?>>
                        </div>
                        <div class="d-none" id="traveling_km_box">
                            <div class="col-10">
                              <label><span style="font-weight:bold;text-transform:uppercase;">Km megadása:</span></label>
                                <input type="number" name="traveling_km" step="10" id="traveling_km" min="0" value="<?php echo $employee['traveling_km']; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="row">
                        <div class="col-2 form-check">
                          <label><span style="font-weight:bold;text-transform:uppercase;">Külföld:</span></label>
                            <input id="foreign" type="checkbox" name="foreign" value="1" <?php if($employee['foreigner'] === '1') { echo "checked"; } ?>>
                        </div>
                        <div class="d-none" id="where_foreign_box">
                            <div class="col-10">
                              <label><span style="font-weight:bold;text-transform:uppercase;">Külföldre hová:</span></label>
                                <textarea name="where_foreign" rows="4" cols="50"><?php echo $employee['where_foreign']; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                      <label><span style="font-weight:bold;text-transform:uppercase;">CV-url:</span></label>
                        <input type="url" name="cv_url" value="<?php echo $employee['cv_url']; ?>">
                </div>
                <div class="form-group">

                          <label><span style="font-weight:bold;text-transform:uppercase;">Megjegyzés: </span></label>
                            <textarea name="comment" rows="4" cols="50" style="width: 100%;"><?php echo $employee['comment']; ?></textarea>

                </div>
                <div id="error" class="alert alert-danger d-none">
                </div>
                <div id="success" class="alert alert-success d-none">
                  <strong>Munkavállaló módosítása sikeres!</strong>
                </div>
                <div class="form-group">
                    <button id="submit-button" style="font-family: 'Barlow', sans-serif;text-transform: uppercase;background-color: #1C75BC;color:#ffffff;font-weight:bold;padding: 10px;border:none;cursor:pointer;border-radius: 5px;">Munkavállaló módosítása</button>
                </div>
            </form>

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
            $('#select-sources').selectize({
                  sortField: 'text'
              });
               $('#select-languages').selectize({
                  sortField: 'text'
              });
               $('#select-driving_license').selectize({
                  sortField: 'text'
              });
          });

        const inputs = document.querySelectorAll('input[data-id="traveling_km"]');
        inputs.forEach(input => {
          input.addEventListener('change', () => {
            if (input.value % 5 !== 0) {
              alert('Csak 5-el osztható számokat lehet megadni!');
              input.value = 10;
            }
          });
        });

    $("#idForm").submit(function(e) {
        $('#submit-button').prop('disabled', true);
        $("#error").addClass('d-none'); 
        $("#error").html('');
        e.preventDefault(); // avoid to execute the actual submit of the form.

        var form = $(this);
        var actionUrl = form.attr('action');

        var modal = document.getElementById('myModal1');
        
        $.ajax({
            type: "POST",
            url: actionUrl,
            data: form.serialize(), // serializes the form's elements.
            //contentType: "application/json; charset=utf-8",
            dataType:"JSON",
            success: function(data)
            {
                if(data === "ok") {
                    $("#success").removeClass('d-none');
                    setTimeout(function() {
                        $("#success").addClass('d-none');
                    }, 5000);
                    $('#submit-button').prop('disabled', false);
            
                } else {
                    $("#error").removeClass('d-none');   
                    $("#error").html(data);
                    $('#submit-button').prop('disabled', false);
                }
            }
        });
        
    });

    $("#logout").on("click", function(e) { 
        $.ajax({
            type: "POST",
            url: "logout.php",
            dataType:"JSON",
            success: function(data)
            {
                if(data === "ok") {
                    window.location.href = "https://mumi.hu/employees/index.php";
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
