<?php
require_once APP_ROOT . '/intranet/inc/header.php';
require_once APP_ROOT . '/intranet/inc/header-reserves-anuals.php';
?>

<div class="container mb-5">
    <h3>Formulari Reserva client Abonament anual</h3>

    <div id="titolForm"></div>

    <form method="POST" id="formReservaAnual" class="row g-3 p-4 bg-light rounded">

        <!-- Missatges -->
        <div class="col-12">
            <div id="okMessage" class="alert alert-success d-none">
                <span id="okText"></span>
            </div>

            <div id="errMessage" class="alert alert-danger d-none">
                <span id="errText"></span>
            </div>
        </div>

        <div class="col-md-4">
            <label for="usuario_uuid_hex" class="form-label">
                Selecciona un client anual <span class="text-danger">*</span>
            </label>

            <select
                class="form-select"
                name="usuario_uuid_hex"
                id="usuario_uuid_hex"
                required>
            </select>

            <div class="form-text">
                <span class="text-danger">*</span> Camp obligatori
            </div>
        </div>

        <div class="col-md-4">
            <label for="tipo_ui" class="form-label">
                Tipus de reserva (anual) <span class="text-danger">*</span>
            </label>

            <select
                class="form-select"
                name="tipo_ui"
                id="tipo_ui"
                disabled>
                <option value="3" selected>Client anual</option>
            </select>

            <div class="form-text">
                <span class="text-danger">*</span> Camp obligatori
            </div>
        </div>

        <div class="col-12">
            <hr>
        </div>

        <div class="col-md-3">
            <label for="diaEntrada" class="form-label">
                Data entrada <span class="text-danger">*</span>
            </label>

            <input
                type="date"
                class="form-control"
                id="diaEntrada"
                name="diaEntrada"
                required
                value="">

            <div class="form-text">
                <span class="text-danger">*</span> Camp obligatori
            </div>
        </div>

        <div class="col-md-3">
            <label for="horaEntrada" class="form-label">
                Hora entrada <span class="text-danger">*</span>
            </label>

            <input
                type="time"
                class="form-control"
                id="horaEntrada"
                name="horaEntrada"
                required
                value="">

            <div class="form-text">
                <span class="text-danger">*</span> Camp obligatori
            </div>
        </div>

        <div class="col-12">
            <hr>
        </div>

        <div class="col-12">
            <h5>Aquests camps són opcionals, els pots modificar més endavant:</h5>
        </div>

        <div class="col-md-3">
            <label for="diaSalida" class="form-label">Data sortida</label>

            <input
                type="date"
                class="form-control"
                id="diaSalida"
                name="diaSalida"
                value="">
        </div>

        <div class="col-md-3">
            <label for="horaSalida" class="form-label">Hora sortida</label>

            <input
                type="time"
                class="form-control"
                id="horaSalida"
                name="horaSalida"
                value="">
        </div>

        <div class="col-md-3">
            <label for="vuelo" class="form-label">Vol</label>

            <input
                type="text"
                class="form-control"
                id="vuelo"
                name="vuelo"
                value="">
        </div>

         <div class="col-md-3"> </div>

        <div class="col-md-4">
            <label for="vehiculo" class="form-label">Model cotxe</label>

            <input
                type="text"
                class="form-control"
                id="vehiculo"
                name="vehiculo"
                value="">
        </div>

        <div class="col-md-4">
            <label for="matricula" class="form-label">Matrícula</label>

            <input
                type="text"
                class="form-control"
                id="matricula"
                name="matricula"
                value="">
        </div>

        <div class="col-md-12">
            <label for="notes" class="form-label">Notes</label>

            <input
                type="text"
                class="form-control"
                id="notes"
                name="notes"
                value="">
        </div>

        <div class="col-12 d-flex flex-column flex-md-row justify-content-between gap-2">

            <a
                href="/control/clients-anuals/"
                class="btn btn-dark menuBtn">
                Tornar
            </a>

            <button
                id="btnReservaAnual"
                name="alta-reserva"
                type="submit"
                class="btn btn-primary">
                Alta reserva
            </button>

        </div>

    </form>
</div>