    
    <div class="container" style="margin-top:50px">

    <div class="col text-center" style="margin-bottom:20px">
            <img src="/public/inc/img/logo2.png" alt="Logo" class="logo d-block mx-auto" width="250">
        </div>

    <div class="card mx-auto" style="max-width: 400px;">

        <div class="card-body">
            <div class="container">
                <h1>Acceso</h1>
                <?php
    echo '<div class="alert alert-success" id="loginMessageOk" style="display:none" role="alert">
                  <h4 class="alert-heading"><strong>Login OK!</strong></h4>
                  <h6>Acceso autorizado, en unos segundos será redirigido a la página de gestión.</h6>
                  </div>';
          
    echo '<div class="alert alert-danger" id="loginMessageErr" style="display:none" role="alert">
                  <h4 class="alert-heading"><strong>Error en los datos</strong></h4>
                  <h6>Usuario o contraseña incorrectos.</h6>
                  </div>';
    ?>
    
                <form action="" method="post" class="login">
                    <label for="email">Usuario</label>
                    <input type="text" name="email" id="email" class="form-control">
                    <br>
    
                    <label for="password">Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control">
                    <br>
                    <button name="login" id="btnLogin" class="btn btn-primary">Login</button>
    
                </form>
                </div>
      </div>
    </div>
    </div>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <script>
$(function () {
  $("#btnLogin").click(function (event) {
    event.preventDefault();

    let email = $("#email").val();
    let password = $("#password").val();

    $.ajax({
      type: "POST",
      url: "https://" + window.location.hostname + "/api/intranet/auth/login/",
      data: { email: email, password: password },
      success: function (response) {
        if (response.status == "success") {
          // Guardar el token en una cookie con las opciones correctas
          // Cookie configurada para que esté disponible en todo el dominio y sea segura
          document.cookie = "token=" + response.token + "; path=/; secure; HttpOnly";

          // Redirigir al home
          window.location = "https://" + window.location.hostname + "/control/home";
        } else {
          // Mostrar error en el login
          alert("Credenciales incorrectas");
        }
      }
    });
  });
});
</script>

