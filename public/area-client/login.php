    
    <div class="container" style="margin-top:50px;margin-bottom:100px">

<div class="card mx-auto" style="max-width: 400px;">

    <div class="card-body">
        <div class="container">
            <h1>Acceso Clientes</h1>
            <h6>Recibirá en su correo electrónico un enlace para acceder al área privada<h6><br>
            <?php
echo '<div class="alert alert-success" id="loginMessageOk" style="display:none" role="alert"></div>';
      
echo '<div class="alert alert-danger" id="loginMessageErr" style="display:none" role="alert"></div>';
?>

            <form action="" method="post" class="login">
                <label for="email">Email</label>
                <input type="text" name="email" id="email" class="form-control">
                <br>

                <button name="login" id="btnLogin" class="btn btn-primary">Solicitar acceso</button>

            </form>
            </div>
  </div>
</div>
</div>

<script>
$(function () {
$("#btnLogin").click(function (event) {
event.preventDefault();

let email = $("#email").val();
const loginMessageOk = document.getElementById('loginMessageOk');
const loginMessageErr = document.getElementById('loginMessageErr');

$.ajax({
  type: "POST",
  url: "https://" + window.location.hostname + "/api/area-client/login",
  data: { email: email },
  success: function (response) {
    if (response.status == "success") {
      loginMessageOk.innerHTML = response.message;
      loginMessageOk.style.display = 'block';
      loginMessageErr.style.display = 'none';

      // Redirigir al home
      setTimeout(function() {
        window.location = "https://" + window.location.hostname + "/area-client";
      }, 2000);
    } else {
      loginMessageErr.innerHTML = response.message;
      loginMessageErr.style.display = 'block';
      loginMessageOk.style.display = 'none';
    }
  }
});
});
});
</script>

