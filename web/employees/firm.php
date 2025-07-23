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

$firms = getFirms($con);

function getFirms($con) {
    $sql="SELECT * FROM `firm` WHERE name<>'' ORDER BY `name` DESC";
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
<script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
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
<title>Cégkereső - mumi.hu</title>
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
              <li><a href="salesman_registrations.php">Kapcsolatfelvétel</a></li>
              <li><a href="firm.php" class="selected">Cégkereső</a></li>
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
            <div class="col-md-12 form-group">
                        <label><span style="font-weight:bold;text-transform:uppercase;">Cég kiválasztása:</span></label>
                        <select class="form-select" id="select-offer" name="firms[]" multiple="multiple" placeholder="Válassz egy céget...">
                             <option value="" style="padding:10px 5px;">Válassz egy céget...</option>
                        <?php 
                        foreach ($firms as $firm) {
                            ?>
                            <option value="<?php echo $firm['id']; ?>"><?php echo $firm['name']; ?></option>
                         <?php 
                        }
                        ?>
                      </select>
            </div>
        </div>
        
        <div class="row align-center">
            <div class="col-auto">
                <button id="submit-button" class="p-1" style="font-family: 'Barlow', sans-serif;text-transform: uppercase;background-color: green;color:#ffffff;font-weight:bold;padding: 10px !important;border:none;cursor:pointer;"><i class="fa fa-search" aria-hidden="true"></i> Keresés</button>
            </div>
            <div id="loading-spinner-top" class="spinner-border text-warning d-none" role="status">
              <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
  </form>
</div>
<div class="row">
  <div id="result" style="display:none;">
  </div>
</div>
<div class="modal hide" id="addBookDialog">
 <div class="modal-header">
    <button class="close" data-dismiss="modal">×</button>
    <h3>Modal header</h3>
  </div>
    <div class="modal-body">
        <p>some content</p>
        <input type="text" name="bookId" id="bookId" value=""/>
    </div>
</div>
    <div class="row table-container">
    <div class="table-responsive">
        <table id="table" class="table table-striped table-bordered compact stripe" style="font-size:12px;"><tr><td class="text-center font-weight-bold text-danger"><span id="warning-text-filter">A cég kiválasztása után jelennek meg adatok!</span> <div id="loading-spinner" class="spinner-border text-warning d-none" role="status">
  <span class="sr-only">Loading...</span>
</div></tr></td></table>
    </div>
    </div>

</div>
<div class="modal hide" id="infromation">
 <div class="modal-header">
    <button class="close" data-dismiss="modal">×</button>
    <h3 id="firmName"></h3>
  </div>
    <div class="modal-body">
        <div class="container">
        <div class="row">
              <div class="col-xs-6">Cég teljes neve:</div>
              <div class="col-xs-6" id="firm_name_long"></div>
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

           $(document).on("click", ".open-information", function () {
            alert('itt');
                 //var firm_name_long = $(this).data('firm_name_long');
                 //$(".modal-body #firm_name_long").innerHtml( firm_name_long );
            });

           $(document).on("click", ".open-AddBookDialog", function () {
     var myBookId = $(this).data('id');
     $(".modal-body #bookId").val( myBookId );
});


               $('#select-offer').selectize({
                  sortField: 'text',
                });
                      

           
    $("#submit-button").click(function(e){
        //e.preventDefault();
        
        
         $("#idForm").submit(function(e) {
          $('#submit-button').prop('disabled', true);
            $('#submit-button-2').prop('disabled', true);
            e.preventDefault(); // avoid to execute the actual submit of the form.
            cursor_wait();
            var form = $(this);
            var actionUrl = form.attr('action');
            $( "#warning-text-filter" ).addClass( "d-none" );
            $( "#loading-spinner" ).addClass( "d-inline-block" );
            $( "#loading-spinner-top" ).addClass( "d-inline-block" );
            $('body').css('cursor', 'progress');
            $.ajax({
                url: "firm_process.php?m=q",
                type: "POST",
                data: form.serialize(), 
                 // serializes the form's elements.
                dataType:"JSON",
            }).done( function(data) {
                remove_cursor_wait();
                
                let table = new $('#table').DataTable( {
                    searching: false,
                    pageLength: 50,
                    destroy: true,
                    responsive: true,
                    rowReorder: {
                        selector: 'td:nth-child(2)'
                    },
                    "aaData": data.records,
                    "columns": [
                        { "data": "id", title: "ID", sortable: true },
                        { "data": "status", title: "Státusz", sortable: false},
                        { "data": "firm_name", title: "Cég név", sortable: true},
                        { "data": "tax_number", title: "Adószám", sortable: true},
                        { "data": "u_name", title: "Kapcsolattartó név", sortable: true},
                        { "data": "email", title: "Kapcsolattartó E-mail", sortable: true },
                        { "data": "phone_number" , title: "Kapcsolattartó telefonszám", sortable: true },
                        { "data": "position", title: "Betöltött pozíció", sortable: true },
                        { "data": "location", title: "Cég székhelye", sortable: true },
                        { "data": "postal_location", title: "Posta cím", sortable: true },
                        { "data": "representative", title: "Cégképviseletre jogosult neve", sortable: true },
                        { "data": "created_at" , title: "Cég létrehozva", sortable: true },
                        { "data": "updated_at" , title: "Cég Módosítva", sortable: true },

                    ],
                    columnDefs: [
                        { className: 'dt-center', targets: '_all' }
                    ],
                    "language": {
                        "lengthMenu": "Megjeleníthető elemek száma oldalanként: _MENU_",
                        "zeroRecords": "Nincs találat",
                        "info": "_PAGE_. oldal a(z) _PAGES_-ből",
                        "infoEmpty": "Nem található cég.",
                        "emptyTable": "Nem található cég.",
                        "infoFiltered": "(szűrt elemek _MAX_ )",
                        "search": 'keresés'
                    },
                    fixedHeader: true,
                    initComplete: function() {
                        //$('#count2').text( this.api().data().length )
                    },
                    order: [[0, 'desc']]
                });
                /*
                $([document.documentElement, document.body]).animate({
                    scrollTop: $("#table").offset().top
                }, 1000); */
                $( "#loading-spinner-top" ).addClass( "d-none" );
                $( "#loading-spinner-top" ).removeClass( "d-inline-block" );
                $('body').css('cursor', 'default');
                $('#submit-button').prop('disabled', false);
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
