    
    <div class="container" style="margin-top:50px;margin-bottom:100px">

    <div class="card mx-auto" style="max-width: 400px;">

        <div class="card-body">
            <div class="container">
                <h1>Accés intranet</h1>
                <?php
    echo '<div class="alert alert-success" id="loginMessageOk" style="display:none" role="alert"></div>';
          
    echo '<div class="alert alert-danger" id="loginMessageErr" style="display:none" role="alert"></div>';
    ?>
    
                <form action="" method="post" class="login">
                    <label for="email">Email</label>
                    <input type="text" name="email" id="email" class="form-control">
                    <br>
    
                    <label for="password">Contrasenya</label>
                    <input type="password" name="password" id="password" class="form-control">
                    <br>
                    <button name="login" id="btnLogin" class="btn btn-primary">Login</button>
    
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
    let password = $("#password").val();
    const loginMessageOk = document.getElementById('loginMessageOk');
    const loginMessageErr = document.getElementById('loginMessageErr');

    $.ajax({
      type: "POST",
      url: "https://" + window.location.hostname + "/api/intranet/auth/login/",
      data: { email: email, password: password },
      success: function (response) {
        if (response.status == "success") {
          loginMessageOk.innerHTML = response.message;
          loginMessageOk.style.display = 'block';
          loginMessageErr.style.display = 'none';

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

