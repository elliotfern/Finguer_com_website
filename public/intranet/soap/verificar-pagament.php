<?php
require_once APP_ROOT . '/public/intranet/inc/header.php';

$idReserva = $routeParams[0];

// ✅ Usamos la nueva función flexible (solo lectura + consulta Redsys)
$response = verificarPagament($idReserva, [
    'solo_info'           => true,
    'actualizar_bd'       => false,
    'enviar_confirmacion' => false,
    'crear_factura'       => false,
    'enviar_factura'      => false,
]);

// Si quieres, puedes sacar info útil:
$paid = (bool)($response['data']['redsys']['paid'] ?? false);
$ds   = (string)($response['data']['redsys']['ds_response'] ?? '');
?>
<div class='container' style='margin-bottom:150px'>
    <h2>Verificar el pagament de la reserva: <?php echo (int)$idReserva; ?></h2>

    <?php
    if (is_array($response) && isset($response['status'], $response['message'])) {

        if ($response['status'] === 'success') {

            // Opcional: mensaje más preciso
            $msg = $response['message'];
            if (isset($response['data']['redsys'])) {
                $msg .= ' (Ds_Response=' . htmlspecialchars($ds) . ')';
            }

            if ($paid) {
                echo "<div class='alert alert-success text-center' role='alert'>
                        <p><img src='" . APP_WEB . "/public/img/correct.png' alt='Pagament OK'></p>
                        <p><strong>" . htmlspecialchars($msg) . "</strong></p>
                      </div>";
            } else {
                // Redsys respondió pero NO está pagado → lo mostramos como aviso/error suave
                echo "<div class='alert alert-danger text-center' role='alert'>
                        <p><img src='" . APP_WEB . "/public/img/warning.png' alt='Pagament No confirmat'></p>
                        <p><strong>" . htmlspecialchars($msg) . "</strong></p>
                      </div>";
            }
        } else {
            echo "<div class='alert alert-danger text-center' role='alert'>
                    <p><img src='" . APP_WEB . "/public/img/warning.png' alt='Pagament Error'></p>
                    <p><strong>" . htmlspecialchars($response['message']) . "</strong></p>
                  </div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center' role='alert'>
                <p><img src='" . APP_WEB . "/public/img/warning.png' alt='Error'></p>
                <p><strong>No se ha podido verificar el pago (respuesta inválida).</strong></p>
              </div>";
    }
    ?>

    <a href="<?php echo APP_WEB; ?>/control/" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>
</div>