<?php require_once APP_ROOT . '/public/intranet/inc/header.php'; ?>

<div class="container-fluid">
    <h2>Estat 1: Reserves pendents d'entrada al párking</h2>
    <h4>Ordenat segons data entrada vehicle</h4>

    <div id="contenidorTaulaReserves"></div>


    <!-- Ventana emergente -->
    <div id="ventanaEmergente" class="ventana" style="display: none; position: absolute; background: white; border: 1px solid #ccc; padding: 20px; border-radius: 8px;">
        <div class="contenidoVentana">
            <div class="container">
                <div class="row">
                    <div class="col-12 col-md-12 d-flex flex-column justify-content-between gap-3">
                        <button id="enlace1" class="btn btn-secondary w-100 w-md-auto btn-sm" role="button" aria-disabled="false">Enviar confirmació</button>

                        <button id="enlace2" class="btn btn-secondary w-100 w-md-auto btn-sm" role="button" aria-disabled="false">Enviar factura</button>

                        <a href="#" id="enlace3" class="btn btn-secondary w-100 w-md-auto btn-sm" role="button" aria-disabled="false">Modificar reserva</a>

                        <a href="#" id="enlace4" class="btn btn-secondary w-100 w-md-auto btn-sm" role="button" aria-disabled="false">Eliminar reserva</a>

                        <button class="btn btn-danger tancar-finestra-btn w-100 w-md-auto btn-sm" role="button" aria-disabled="false">Tancar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="margin-bottom:50px">
        <h5 id="numReserves"></h5>
    </div>

</div>

<style>
    /* Contenedor con scroll vertical si la tabla es muy alta */
    .table-responsive {
        max-height: 90vh;
        /* ajusta la altura que quieras */
        overflow-y: auto;
        /* scroll vertical */
    }

    /* Cabecera fija */
    .table-responsive thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        /* para que quede por encima del contenido */
    }

    /* Aseguramos fondo para que no se vea el texto de atrás al hacer scroll */
    .table-responsive thead th {
        background-color: #212529;
        /* mismo color que .table-dark de Bootstrap */
        color: #fff;
    }
</style>