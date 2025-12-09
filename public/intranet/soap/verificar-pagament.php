<?php
require_once APP_ROOT . '/public/intranet/inc/header.php';

$idReserva = $routeParams[0];
$response = verificarPagamentRedsys($idReserva, false);

?>
<div class='container' style='margin-bottom:150px'>
    <h2>Verificar el pagament de la reserva: <?php echo $idReserva; ?></h2>

    <?php
    // Verificar si el JSON se decodificó correctamente
    if ($response && isset($response['status']) && isset($response['message'])) {
        if ($response['status'] === 'success') {
            // Mostrar mensaje de éxito
            echo "<div class='alert alert-success text-center' role='alert'>
                <p><img src='" . APP_WEB . "/public/img/correct.png' alt='Pagament OK'></p>
                <p><strong>" . htmlspecialchars($response['message']) . "</strong></p>
              </div>";
        } elseif ($response['status'] === 'error') {
            // Mostrar mensaje de error
            echo "<div class='alert alert-danger text-center' role='alert'>
                <p><img src='" . APP_WEB . "/public/img/warning.png' alt='Pagament Error'></p>
                <p><strong>" . htmlspecialchars($response['message']) . "</strong></p>
              </div>";
        }
    }
    ?>

    <a href="<?php echo APP_WEB; ?>/control/" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>

</div>