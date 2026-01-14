<?php require_once APP_ROOT . '/public/intranet/inc/header.php'; ?>

<div class="container" style="margin-bottom:100px">
    <h2>Gestió clients i usuaris web</h2>

    <div id="titolReservesClient"></div>
    <div id="contenidorReservesClient"></div>

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