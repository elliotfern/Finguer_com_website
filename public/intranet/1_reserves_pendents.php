<?php require_once APP_ROOT . '/public/intranet/inc/header.php'; ?>

<div class="container">
    <h2>Estat 1: Reserves pendents d'entrada al párking</h2>
    <h4>Ordenat segons data entrada vehicle</h4>
</div>

<div class="container">
    <div class='table-responsive'>
        <table class='table table-striped' id="pendents">
            <thead class="table-dark">
                <tr>
                    <th>Núm. Comanda // data</th>
                    <th>Import</th>
                    <th>Pagat</th>
                    <th>Tipus</th>
                    <th>Neteja</th>
                    <th>Client // tel.</th>
                    <th>Entrada &darr;</th>
                    <th>Sortida</th>
                    <th>Dades Vehicle</th>
                    <th>Vol tornada</th>
                    <th>Check-in</th>
                    <th>Notes</th>
                    <th>Cercadors</th>
                    <th>Opcions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

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
    <h5 id="numReservesPendents"></h5>
</div>