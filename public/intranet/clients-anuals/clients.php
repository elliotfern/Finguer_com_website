<?php
require_once APP_ROOT . '/intranet/inc/header.php';
require_once(APP_ROOT . '/intranet/inc/header-reserves-anuals.php');
?>

<div class='container' style='margin-bottom:100px'>
    <h3>Clients amb Abonament anual</h3>

    <div class="table-responsive">
        <table class="table table-striped" id="taula-clients-anuals">
            <thead class="table-dark">
                <tr>
                    <th>Nom i cognoms ↓</th>
                    <th>Telèfon</th>
                    <th>Fi Anualitat</th>
                    <th>Reserves completades</th>
                    <th>Estat</th>
                    <th>Modificar dades</th>
                    <th>Eliminar client</th>
                    <th>Crear reserva</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

</div>