<?php
require_once APP_ROOT . '/intranet/inc/header.php';
require_once(APP_ROOT . '/intranet/inc/header-reserves-anuals.php');
?>

<div class='container' style='margin-bottom:100px'>
    <h3>Formulari client Abonament anual</h3>

    <form action="" id="formclientAnual" class="row g-3 p-4 bg-light rounded">

        <h5 class="mb-3">Dades obligatòries del client anual</h5>

        <!-- Nombre -->
        <div class="col-md-4">
            <label class="form-label">Nom i cognoms <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre" value="" required>
            <div class="form-text text-danger">Camp obligatori</div>
        </div>

        <!-- Email -->
        <div class="col-md-4">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="email" value="" required>
            <div class="form-text text-danger">Camp obligatori</div>
        </div>

        <!-- Teléfono -->
        <div class="col-md-4">
            <label class="form-label">Telèfon <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="telefono" value="" required>
            <div class="form-text text-danger">Camp obligatori</div>
        </div>

        <!-- Idioma -->
        <div class="col-md-4">
            <label class="form-label">Idioma</label>
            <select class="form-select" name="locale" required>
                <option value="ca">Català</option>
                <option value="es" selected>Espanyol</option>
                <option value="fr">Francès</option>
                <option value="en">Anglès</option>
                <option value="it">Italià</option>
            </select>
        </div>

        <!-- Fecha inicio anualidad -->
        <div class="col-md-4">
            <label class="form-label">Inici anualitat <span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="fecha_inicio" value="" required>
        </div>

        <!-- Fecha fin anualidad -->
        <div class="col-md-4">
            <label class="form-label">Fi anualitat <span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="fecha_fin" value="" required>
        </div>

        <hr class="my-3">

        <h5 class="mb-3">Dades del vehicle</h5>

        <!-- Vehículo -->
        <div class="col-md-6">
            <label class="form-label">Vehicle</label>
            <input type="text" class="form-control" name="vehiculo" value="">
        </div>

        <!-- Matrícula -->
        <div class="col-md-6">
            <label class="form-label">Matrícula <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="matricula" value="" required>
        </div>

        <!-- Observaciones -->
        <div class="col-12">
            <label class="form-label">Observacions</label>
            <textarea class="form-control" name="observaciones" value="" rows="3"></textarea>
        </div>

        <hr class="my-3">

        <h5 class="mb-3">Dades opcionals del client</h5>

        <!-- Empresa -->
        <div class="col-md-4">
            <label class="form-label">Empresa</label>
            <input type="text" class="form-control" name="empresa" value="">
        </div>

        <!-- NIF -->
        <div class="col-md-4">
            <label class="form-label">NIF</label>
            <input type="text" class="form-control" name="nif" value="">
        </div>

        <!-- Dirección -->
        <div class="col-md-4">
            <label class="form-label">Direcció</label>
            <input type="text" class="form-control" name="direccion" value="">
        </div>

        <!-- Ciudad -->
        <div class="col-md-4">
            <label class="form-label">Ciutat</label>
            <input type="text" class="form-control" name="ciudad" value="">
        </div>

        <!-- CP -->
        <div class="col-md-4">
            <label class="form-label">Codi postal</label>
            <input type="text" class="form-control" name="codigo_postal" value="">
        </div>

        <!-- País -->
        <div class="col-md-4">
            <label class="form-label">País</label>
            <input type="text" class="form-control" name="pais" value="">
        </div>

        <hr class="my-3">

        <h5 class="mb-3">Pla anual</h5>

        <!-- Límite de reservas -->
        <div class="col-md-4">
            <label class="form-label">Límit de reserves</label>
            <input type="number" class="form-control" name="limite_reservas" value="10" min="1" value="">
            <div class="form-text">Per defecte: 10 reserves / anualitat</div>
        </div>

        <!-- Estado abono -->
        <div class="col-md-4">
            <label class="form-label">Estat abonament</label>
            <select class="form-select" name="abono_estado">
                <option value="activo">Actiu</option>
                <option value="pendiente">Pendent</option>
                <option value="caducado">Caducat</option>
                <option value="suspendido">Suspès</option>
            </select>
        </div>

        <div class="col-12 d-flex justify-content-between mt-3">

            <a href="/control/clients-anuals/" class="btn btn-outline-secondary">
                Tornar
            </a>

            <button type="submit" id="btnAnual" class="btn btn-primary">
                Alta client anual
            </button>

        </div>

    </form>

</div>