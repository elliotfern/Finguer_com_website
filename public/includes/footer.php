<?php
$bundleUrl  = BASE_URL . '/dist/bundle.js';       // ruta en URL
$bundlePath = BASE_URL . '/dist/bundle.js';         // ruta en disco

$v = file_exists($bundlePath) ? filemtime($bundlePath) : time();
?>


<div class="container-fluid" style="background-color: #02164f">
    <div class="container" style="padding:50px;color:white">
        <div class="row justify-content-center">

            <div class="col-12 col-md-2">
                <img class="img-responsive" src="<?php APP_ROOT; ?>/public/img/logo-header-sticky.png" alt="Finguer Parking Aeropuerto El Prat de Barcelona">
            </div>

            <div class="col-12 col-md-2">
                <h6><strong>CONTACTO</strong></h6>
                <p class="has-small-font-size"><a href="tel:+34689255821">+34 689 255 821</a><br>Carrer de l'Alt Camp, 9,<br>08830 Sant Boi de Llobregat,<br>Barcelona</p>
            </div>

            <div class="col-12 col-md-2">
                <h6><strong>LEGAL</strong></h6>
                <p><a href="/terminos-y-condiciones/">Terminos y Condiciones</a><br>
                    <a href="/politica-de-privacidad-finguer/">Política de Privacidad</a>
                </p>
            </div>

            <div class="col-12 col-md-2">
                <h6><strong>AUTO GESTIO FERCAR SL</strong></h6>
                <p>Carrer de l'Alt Camp 9<br>08830 Sant Boi de Llobregat (Barcelona)<br>NIF B64768997</p>
            </div>

            <div class="col-12 col-md-2">
                <h6><strong>SÍGUENOS</strong></h6>
                <a href="https://www.instagram.com/finguer_parking/" target="_blank"><img src="<?php APP_ROOT; ?>/public/img/icons-instagram.svg" alt="Instagram" class="img-responsive"></a>
            </div>

        </div>
    </div>
</div>

<script type="module" src="<?= htmlspecialchars($bundleUrl, ENT_QUOTES) ?>?v=<?= $v ?>"></script>
<script src="<?php APP_ROOT; ?>/public/js/cookies.js"></script>
<script>
    window.APP_REDSYS_URL = "<?= htmlspecialchars($_ENV['URLREDSYS'] ?? '', ENT_QUOTES) ?>";
</script>
</body>

</html>