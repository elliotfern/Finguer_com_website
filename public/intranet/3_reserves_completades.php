<?php require_once APP_ROOT . '/public/intranet/inc/header.php'; ?>

<div class="container-fluid">
    <h2>Estat 3: Reserves completades</h2>
    <h4>Només es mostren les últimes 20 completades</h4>

    <div id="contenidorTaulaReserves"></div>

    <!-- Ventana emergente -->
    <div id="ventanaEmergente" class="ventana" style="display: none; position: absolute; background: white; border: 1px solid #ccc; padding: 20px; border-radius: 8px;">
        <div class="contenidoVentana">
            <div class="container">
                <div class="row">
                    <div class="col-12 col-md-12 d-flex flex-column justify-content-between gap-3">
                        <button id="enlace1" class="btn btn-secondary  w-100 w-md-auto btn-sm" role="button" aria-disabled="false">Enviar confirmació</button>

                        <button id="enlace2" class="btn btn-secondary  w-100 w-md-auto btn-sm" role="button" aria-disabled="false">Enviar factura</button>

                        <a href="#" id="enlace3" class="btn btn-secondary  w-100 w-md-auto btn-sm" role="button" aria-disabled="false">Modificar reserva</a>

                        <button id="enlace4" class="btn btn-secondary  w-100 w-md-auto btn-sm" role="button" aria-disabled="false">Eliminar reserva</button>

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