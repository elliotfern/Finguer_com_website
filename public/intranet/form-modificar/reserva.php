<?php require_once APP_ROOT . '/intranet/inc/header.php'; ?>


<div class="container" style="margin-bottom:50px">
     <div id="titolForm"></div>

    <form id="reservaForm" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">

    <div id="okMessage" class="alert alert-success d-none" role="alert">
    <div id="okText"></div>
        </div>

        <div id="errMessage" class="alert alert-danger d-none" role="alert">
            <div id="errText"></div>
        </div>

        <input type="hidden" id="id" name="id">
        <input type="hidden" id="usuario_uuid" name="usuario_uuid">

        <h4>Dades de la reserva:</h4>

        <!-- Localizador -->
        <div class="col-md-4">
            <label for="localizador" class="form-label">Localitzador:</label>
            <input type="text" class="form-control" id="localizador" name="localizador" readonly>
            <small class="form-text text-danger">* No es pot modificar</small>
        </div>

        <!-- Estado -->
        <div class="col-md-4">
            <label for="estado" class="form-label">Estat de la reserva:</label>
            <select class="form-select" id="estado" name="estado">
                <!-- Opciones rellenadas dinámicamente vía TypeScript -->
            </select>
        </div>

        <!-- Estado vehículo -->
        <div class="col-md-4">
            <label for="estado_vehiculo" class="form-label">Estat del vehicle:</label>
            <select class="form-select" id="estado_vehiculo" name="estado_vehiculo">
                <!-- Opciones rellenadas dinámicamente vía TypeScript -->
            </select>
        </div>

        <!-- Tipo -->
        <div class="col-md-4">
            <label for="tipo" class="form-label">Tipus de reserva:</label>
            <select class="form-select" id="tipo" name="tipo">
                <!-- Opciones rellenadas dinámicamente vía TypeScript -->
            </select>
        </div>

        <!-- Fecha reserva -->
        <div class="col-md-4">
            <label for="fecha_reserva" class="form-label">Data de la reserva:</label>
            <input type="text" class="form-control" id="fecha_reserva" name="fecha_reserva" readonly>
            <small class="form-text text-danger">* No es pot modificar</small>
        </div>

        <!-- Canal -->
        <div class="col-md-4">
            <label for="canal" class="form-label">Canal:</label>
            <select class="form-select" id="canal" name="canal">
                <!-- Opciones rellenadas dinámicamente vía TypeScript -->
            </select>
        </div>

        <hr>
        <h4>Entrada i sortida:</h4>

        <!-- Entrada prevista -->
        <div class="col-md-6">
            <label for="entrada_prevista" class="form-label">Entrada prevista:</label>
            <input type="datetime-local" class="form-control" id="entrada_prevista" name="entrada_prevista">
        </div>

        <!-- Salida prevista -->
        <div class="col-md-6">
            <label for="salida_prevista" class="form-label">Sortida prevista:</label>
            <input type="datetime-local" class="form-control" id="salida_prevista" name="salida_prevista">
        </div>

        <hr>
        <h4>Vehicle i ocupants:</h4>

        <!-- Vehículo -->
        <div class="col-md-4">
            <label for="vehiculo" class="form-label">Model vehicle:</label>
            <input type="text" class="form-control" id="vehiculo" name="vehiculo">
        </div>

        <!-- Matrícula -->
        <div class="col-md-4">
            <label for="matricula" class="form-label">Número de matrícula:</label>
            <input type="text" class="form-control" id="matricula" name="matricula">
        </div>

        <!-- Personas -->
        <div class="col-md-4">
            <label for="personas" class="form-label">Nombre de persones:</label>
            <input type="number" class="form-control" id="personas" name="personas" min="1">
        </div>

        <!-- Vuelo -->
        <div class="col-md-4">
            <label for="vuelo" class="form-label">Vol client:</label>
            <input type="text" class="form-control" id="vuelo" name="vuelo">
        </div>

        <hr>
        <h4>Import (calculat pel sistema):</h4>

        <!-- Subtotal -->
        <div class="col-md-4">
            <label for="subtotal_calculado" class="form-label">Subtotal:</label>
            <input type="text" class="form-control" id="subtotal_calculado" name="subtotal_calculado" readonly>
            <small class="form-text text-danger">* No es pot modificar</small>
        </div>

        <!-- IVA -->
        <div class="col-md-4">
            <label for="iva_calculado" class="form-label">IVA:</label>
            <input type="text" class="form-control" id="iva_calculado" name="iva_calculado" readonly>
            <small class="form-text text-danger">* No es pot modificar</small>
        </div>

        <!-- Total -->
        <div class="col-md-4">
            <label for="total_calculado" class="form-label">Total:</label>
            <input type="text" class="form-control" id="total_calculado" name="total_calculado" readonly>
            <small class="form-text text-danger">* No es pot modificar</small>
        </div>

        <hr>

        <!-- Notas -->
        <div class="col-12">
            <label for="notas" class="form-label">Nota reserva:</label>
            <textarea class="form-control" id="notas" name="notas" rows="3"></textarea>
        </div>

        <div class="col-12 d-flex justify-content-between mt-3">
            <a href="/inici" class="btn btn-outline-secondary">Tornar</a>
            <button id="btnReserva" type="submit" class="btn btn-primary">
                Modificar reserva
            </button>
        </div>

    </form>
</div>
