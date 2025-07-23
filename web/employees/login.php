<!doctype html>
<html lang="hu">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>Bejelentkezés</title>
  </head>
  <body style="font-family: 'Barlow', sans-serif;font-size:12px;">
    <div class="container align-middle mt-5">
	<center><img src="https://mumi.hu/images/mumi-email_logo.png" /><br><br></center>
      <div class="d-flex justify-content-center align-items-center">
	  
	  
        <form id="idForm" action="auth.php" method="POST">
          <!-- Email input -->
          <div class="form-outline mb-4">
           <center> <label class="form-label" for="logName">Felhasználónév:</label>
			<input type="input" id="logName" class="form-control" name="logName" />
            </center>
          </div>

          <!-- Password input -->
          <div class="form-outline mb-4">
             <center>
			 <label class="form-label" for="paswdr">Jelszó:</label>
			 <input type="password" id="paswdr" class="form-control" name="paswdr" />
            
			</center>
          </div>

          <!-- Submit button -->
          <button id="submit-button" type="submit" class="btn btn-primary btn-block mb-4" style="background-color:#F79F39;border:none;font-weight: bold;text-transform:uppercase;">Bejelentkezés</button>
          <div id="error" style="color:red;font-szie:16px">
          <div>
        </form>
      </div>
    </div>

    <!-- Optional JavaScript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script>
      $(document).ready(function () {
         $("#idForm").submit(function(e) {
          e.preventDefault(); 
          $("#error").html();
          $('#submit-button').prop('disabled', false);

          var form = $(this);
          var actionUrl = form.attr('action');
          $.ajax({
              type: "POST",
              url: actionUrl,
              data: form.serialize(), // serializes the form's elements.
              dataType:"JSON",
              success: function(data)
              {
                console.log(data);
                if(data !== "ok") {
                  $("#error").html(data);
                  $('#submit-button').prop('disabled', false);
                } else {
                    window.location.href = "https://mumi.hu/employees/index.php";
                }
               
              }
          });
          
      });
    });
  </script>
  </body>
</html>