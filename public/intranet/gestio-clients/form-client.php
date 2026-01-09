<?php

declare(strict_types=1);

require_once APP_ROOT . '/public/intranet/inc/header.php';

/**
 * Este archivo SOLO dibuja el formulario.
 * - Modo create/update lo decide TS (por querystring o por llamada explícita).
 * - "estado" va fijo a activo.
 */
?>

<div class="container"
    style="margin-bottom:100px;border:1px solid gray;border-radius:10px;padding:25px;background-color:#eaeaea">
    <div class="container">
        <div class="row">

            <!-- Título dinámico (lo rellenará TS) -->
            <div id="titolForm"></div>

            <!-- OK / ERROR -->
            <div class="alert alert-success" role="alert" id="okMessage" style="display:none">
                <div id="okText"></div>
            </div>

            <div class="alert alert-danger" role="alert" id="errMessage" style="display:none">
                <div id="errText"></div>
            </div>

            <form id="UsuariosForm" autocomplete="off">
                <!-- Hidden (update) -->
                <input type="hidden" name="uuid" id="uuid" value="">

                <!-- Estado fijo en esta pantalla -->
                <input type="hidden" name="estado" id="estado" value="activo">

                <!-- =========================
             BLOQUE: Identidad
        ========================== -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label negreta">Nombre y apellidos *</label>
                        <input type="text"
                            class="form-control"
                            name="nombre"
                            id="nombre"
                            required
                            maxlength="255"
                            value="">
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label negreta">Email *</label>
                        <input type="email"
                            class="form-control"
                            name="email"
                            id="email"
                            required
                            maxlength="255"
                            value="">
                        <div class="form-text">Se normaliza a minúsculas al guardar.</div>
                    </div>
                </div>

                <!-- =========================
             BLOQUE: Acceso
        ========================== -->
                <div class="row espai-superior" style="margin-top:20px;">
                    <div class="col-md-6">
                        <label for="password" class="form-label negreta">Contraseña</label>
                        <input type="password"
                            class="form-control"
                            name="password"
                            id="password"
                            minlength="8"
                            autocomplete="new-password"
                            value="">
                        <div class="form-text">
                            En creación: si la dejas vacía, el usuario no tendrá contraseña.<br>
                            En edición: si la dejas vacía, no se modifica.
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="tipo_rol" class="form-label negreta">Tipo de cliente*</label>
                        <select class="form-select" name="tipo_rol" id="tipo_rol" required>
                            <option value="cliente" selected>Cliente web</option>
                            <option value="cliente_anual">Cliente anual</option>
                            <option value="trabajador">Trabajador</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="locale" class="form-label negreta">Idioma cliente *</label>
                        <select class="form-select" name="locale" id="locale" required>
                            <option value="ca">Català</option>
                            <option value="es" selected>Castellano</option>
                            <option value="fr">Français</option>
                            <option value="en">English</option>
                            <option value="it">Italiano</option>
                        </select>
                    </div>
                </div>

                <!-- =========================
             BLOQUE: Contacto / Facturación
        ========================== -->
                <div class="row espai-superior" style="border-top:1px solid black;padding-top:25px;margin-top:25px;">

                    <h5>Datos de facturación (opcionales): </h5>
                    <div class="col-md-4">
                        <label for="telefono" class="form-label negreta">Teléfono</label>
                        <input type="text" class="form-control" name="telefono" id="telefono" maxlength="50" value="">
                    </div>

                    <div class="col-md-4">
                        <label for="pais" class="form-label negreta">País</label>
                        <input type="text" class="form-control" name="pais" id="pais" maxlength="100" value="">
                    </div>

                    <div class="col-md-4">
                        <label for="empresa" class="form-label negreta">Empresa</label>
                        <input type="text" class="form-control" name="empresa" id="empresa" maxlength="255" value="">
                    </div>

                    <div class="col-md-4">
                        <label for="nif" class="form-label negreta">NIF</label>
                        <input type="text" class="form-control" name="nif" id="nif" maxlength="50" value="">
                    </div>

                    <div class="col-md-6">
                        <label for="direccion" class="form-label negreta">Dirección</label>
                        <input type="text" class="form-control" name="direccion" id="direccion" maxlength="255" value="">
                    </div>

                    <div class="col-md-4">
                        <label for="ciudad" class="form-label negreta">Ciudad</label>
                        <input type="text" class="form-control" name="ciudad" id="ciudad" maxlength="150" value="">
                    </div>

                    <div class="col-md-3">
                        <label for="codigo_postal" class="form-label negreta">Código postal</label>
                        <input type="text" class="form-control" name="codigo_postal" id="codigo_postal" maxlength="20" value="">
                    </div>

                    <hr>
                    <h5>Información para Clientes anuales: </h5>

                    <div class="col-md-4">
                        <label for="anualitat" class="form-label negreta">Anualidad (dia/mes/año):</label>
                        <input type="text" class="form-control" name="anualitat" id="anualitat" maxlength="50" value="">
                    </div>
                </div>

                <!-- =========================
             BOTÓN
        ========================== -->
                <div class="row espai-superior" style="border-top:1px solid black;padding-top:25px;margin-top:25px;">
                    <div class="col"></div>
                    <div class="col d-flex justify-content-end align-items-center">
                        <button class="btn btn-primary" id="btnUsuarios" type="submit">
                            Guardar
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>