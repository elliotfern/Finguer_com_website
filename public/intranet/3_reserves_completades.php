<?php require_once APP_ROOT . '/public/intranet/inc/header.php'; ?>

<div class="container-fluid">
    <h2>Estat 3: Reserves completades</h2>
    <h4>Només es mostren les últimes 20 completades</h4>

    <div id="contenidorTaulaReserves"></div>

     <!-- Ventana emergente (solo contenedor) -->
    <div id="ventanaEmergente"
        class="ventana"
        style="display:none; position:absolute; background:white; border:1px solid #ccc; padding:20px; border-radius:8px;">
    </div>

    <div class="container" style="margin-bottom:50px">
        <h5 id="numReserves"></h5>
    </div>

</div>