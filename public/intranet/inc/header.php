<div class="container">
<?php
    // Genera o obtén tu clave secreta

    if (isset($_COOKIE['user_id'])) {
        $userId = $_COOKIE['user_id'];
    }
    $loggedInUser = $userId;
    ?>
      <span class="d-flex align-items-center text-decoration-none">
        <strong><div id="userDiv" class="white" style="margin-top:20px"> </div></strong>
    </span>     
    <a href="#" class="links-sidebar link-sortir" onclick="logout()">Sortir de la intranet</a>
  </div>

    <div class="container text-center">
    <div class="row">

        <div class="col-sm">
        <a href="<?php APP_SERVER;?>/control/home" class="btn btn-warning menuBtn" role="button" aria-disabled="false">Estat 1: pendent</a>
        </div>

        <div class="col-sm">
        <a href="<?php APP_SERVER;?>/control/reserves-parking" class="btn btn-danger menuBtn" role="button" aria-disabled="false">Estat 2: al parking</a>
        </div>
        <div class="col-sm">
        <a href="<?php APP_SERVER;?>/control/reserves-completades" class="btn btn-success menuBtn" role="button" aria-disabled="false">Estat 3: completades</a>
        </div>
    </div>

    <div class="row" style="margin-top:20px;margin-bottom:20px">
        <div class="col-sm">
        <a href="<?php APP_SERVER;?>/control/cercador-reserva" class="btn btn-secondary menuBtn" role="button" aria-disabled="false">Cercador reserva</a>
        </div>

        <div class="col-sm">
        <a href="<?php APP_SERVER;?>/control/calendari/entrades" class="btn btn-secondary menuBtn" role="button" aria-disabled="false">Calendari entrades</a>
        </div>

        <div class="col-sm">
        <a href="<?php APP_SERVER;?>/control/calendari/sortides" class="btn btn-secondary menuBtn" role="button" aria-disabled="false">Calendari sortides</a>
        </div>

        <div class="col-sm">
        <a href="<?php APP_SERVER;?>/control/cercadors/" class="btn btn-secondary menuBtn" role="button" aria-disabled="false">Buscadors</a>
        </div>

        <div class="col-sm">
        <a href="<?php APP_SERVER;?>/control/clients-anuals" class="btn btn-secondary menuBtn" role="button" aria-disabled="false">Clients anuals</a>
        </div>

    </div>

    </div>
    </div>
    <hr>

    <script>
        nameUser('<?php echo $loggedInUser; ?>')

       function nameUser(idUser) {
        let urlAjax =  "https://" + window.location.hostname + "/api/intranet/users/get/?type=user&id=" + idUser;
        $.ajax({
            url: urlAjax,
            type: 'GET',
            success: function (data) {
               let responseData = data;  // Parsea la respuesta JSON
                let welcomeMessage = responseData.nombre ? `Benvingut, ${responseData.nombre}` : 'User not found';
                $('#userDiv').html(welcomeMessage);
            },
            error: function (error) {
                console.error('Error: ' + JSON.stringify(error));
            }
        });
    }

// Función de logout
function logout() {
    let urlAjax =  "https://" + window.location.hostname + "/api/intranet/users/get/?type=deleteCookies";
        $.ajax({
            url: urlAjax,
            type: 'GET',
            success: function (data) {
                 window.location.href = '/control/login'
            },
            error: function (error) {
                console.error('Error: ' + JSON.stringify(error));
            }
        });
}
</script>