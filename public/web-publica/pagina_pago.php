<?php
// Obtener el idioma actual desde la cookie
$currentLanguage = $_COOKIE['language'] ?? 'es';  // Si no está establecido, por defecto 'es'

// Obtener traducciones generales
$pago = $translations['paginaPago'] ?? [];
?>


<div class="container" id="pantallaPagamentError" style="display:none;margin-bottom:400px;margin-top:100px">
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading"><?php echo $pago['error']; ?></h4>
        <p><?php echo $pago['errorMessage']; ?></p>
    </div>

    <!-- Botón para volver a la página de inicio -->
    <a href="/" class="btn btn-primary"><?php echo $pago['volver']; ?></a>
</div>

<div class="container-fluid" id="pantallaPagament">
    <div class="container">

        <div class="row">
            <div class="col-12 col-md-6" style="padding:25px">
                <div class="container">

                    <div class="row">

                        <div class="alert alert-success" id="messageOk" style="display:none" role="alert">
                            <h4 class="alert-heading"><strong>Todo OK</strong></h4>
                            <h6>Datos guardados</h6>
                        </div>

                        <div class="alert alert-danger" id="messageErr" style="display:none" role="alert">
                            <h4 class="alert-heading"><strong>¡Error!</strong></h4>
                            <h6>Por favor revise los datos que ha introducido antes de completar el pedido.</h6>
                        </div>

                        <h3><?php echo $pago['cliente']; ?></h3>
                        <div class="col-md-6">
                            <label for="nombre"><?php echo $pago['nombre']; ?></label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                            <div class="invalid-feedback" id="error-nombre"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="email"><?php echo $pago['email']; ?></label>
                            <input type="text" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback" id="error-email"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="telefono"><?php echo $pago['telefono']; ?></label>
                            <input type="text" class="form-control" id="telefono" name="telefono" required>
                            <div class="invalid-feedback" id="error-telefono"></div>
                        </div>

                        <div class="row g-3">
                            <br>
                            <h3><?php echo $pago['vehiculo']; ?></h3>
                            <div class="col-md-6">
                                <label for="modelo_vehiculo"><?php echo $pago['vehiculo']; ?></label>
                                <input type="text" class="form-control" id="vehiculo" name="vehiculo">
                                <div id="error-vehiculo" class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="matricula"><?php echo $pago['matricula']; ?></label>
                                <input type="text" class="form-control" id="matricula" name="matricula">
                                <div id="error-matricula" class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="vuelo_retorno"><?php echo $pago['vuelo']; ?></label>
                                <input type="text" class="form-control" id="vuelo" name="vuelo">
                                <div id="error-vuelo" class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="numero_personas"><?php echo $pago['acompanantes']; ?></label>
                                <input type="number" class="form-control" id="numero_personas" name="numero_personas" min="1" max="100" required>
                                <div id="numero_personasHelp" class="form-text">*Si vais a ser más de 8 personas, por favor, avisad antes al párking.</div>
                                <div id="error-numero_personas" class="invalid-feedback"></div>
                            </div>

                        </div>

                        <h3 style="margin-top:30px"><?php echo $pago['facturacion']; ?></h3>
                        <div class="alert alert-dark" role="alert">
                            Sólo rellene estos datos si necesita la factura.
                        </div>
                        <div class="col-md-6">
                            <label for="empresa"><?php echo $pago['empresa']; ?></label>
                            <input type="text" class="form-control" id="empresa" name="empresa">
                        </div>

                        <div class="col-md-6">
                            <label for="nif"><?php echo $pago['nif']; ?></label>
                            <input type="text" class="form-control" id="nif" name="nif">
                        </div>

                        <div class="col-md-6">
                            <label for="direccion"><?php echo $pago['direccion']; ?></label>
                            <input type="text" class="form-control" id="direccion" name="direccion">
                        </div>

                        <div class="col-md-6">
                            <label for="ciudad"><?php echo $pago['ciudad']; ?></label>
                            <input type="text" class="form-control" id="ciudad" name="ciudad">
                        </div>

                        <div class="col-md-6">
                            <label for="codigo_postal"><?php echo $pago['codigo_postal']; ?></label>
                            <input type="text" class="form-control" id="codigo_postal" name="codigo_postal">
                        </div>

                        <div class="col-md-6">
                            <label for="pais" class="form-label"><?= $pago['pais']; ?></label>
                            <select class="form-select" name="pais" id="pais">
                                <option value=""><?= $pago['seleccion_pais']; ?></option>
                                <option value="España"><?= $pago['espana']; ?></option>
                                <option value="Albania"><?= $pago['albania']; ?></option>
                                <option value="Alemania"><?= $pago['alemania']; ?></option>
                                <option value="Andorra"><?= $pago['andorra']; ?></option>
                                <option value="Austria"><?= $pago['austria']; ?></option>
                                <option value="Bélgica"><?= $pago['belgica']; ?></option>
                                <option value="Bielorrusia"><?= $pago['bielorrusia']; ?></option>
                                <option value="Bosnia y Herzegovina"><?= $pago['bosnia']; ?></option>
                                <option value="Bulgaria"><?= $pago['bulgaria']; ?></option>
                                <option value="Chipre"><?= $pago['chipre']; ?></option>
                                <option value="Croacia"><?= $pago['croacia']; ?></option>
                                <option value="Dinamarca"><?= $pago['dinamarca']; ?></option>
                                <option value="Eslovaquia"><?= $pago['eslovaquia']; ?></option>
                                <option value="Eslovenia"><?= $pago['eslovenia']; ?></option>
                                <option value="Estonia"><?= $pago['estonia']; ?></option>
                                <option value="Finlandia"><?= $pago['finlandia']; ?></option>
                                <option value="Francia"><?= $pago['francia']; ?></option>
                                <option value="Grecia"><?= $pago['grecia']; ?></option>
                                <option value="Hungría"><?= $pago['hungria']; ?></option>
                                <option value="Irlanda"><?= $pago['irlanda']; ?></option>
                                <option value="Islandia"><?= $pago['islandia']; ?></option>
                                <option value="Italia"><?= $pago['italia']; ?></option>
                                <option value="Kosovo"><?= $pago['kosovo']; ?></option>
                                <option value="Letonia"><?= $pago['letonia']; ?></option>
                                <option value="Liechtenstein"><?= $pago['liechtenstein']; ?></option>
                                <option value="Lituania"><?= $pago['lituania']; ?></option>
                                <option value="Luxemburgo"><?= $pago['luxemburgo']; ?></option>
                                <option value="Malta"><?= $pago['malta']; ?></option>
                                <option value="Moldavia"><?= $pago['moldavia']; ?></option>
                                <option value="Mónaco"><?= $pago['monaco']; ?></option>
                                <option value="Montenegro"><?= $pago['montenegro']; ?></option>
                                <option value="Noruega"><?= $pago['noruega']; ?></option>
                                <option value="Países Bajos"><?= $pago['paises_bajos']; ?></option>
                                <option value="Polonia"><?= $pago['polonia']; ?></option>
                                <option value="Portugal"><?= $pago['portugal']; ?></option>
                                <option value="Reino Unido"><?= $pago['reino_unido']; ?></option>
                                <option value="República Checa"><?= $pago['republica_checa']; ?></option>
                                <option value="República de Macedonia del Norte"><?= $pago['macedonia']; ?></option>
                                <option value="Rumanía"><?= $pago['rumania']; ?></option>
                                <option value="Rusia"><?= $pago['rusia']; ?></option>
                                <option value="San Marino"><?= $pago['san_marino']; ?></option>
                                <option value="Serbia"><?= $pago['serbia']; ?></option>
                                <option value="Suecia"><?= $pago['suecia']; ?></option>
                                <option value="Suiza"><?= $pago['suiza']; ?></option>
                                <option value="Ucrania"><?= $pago['ucrania']; ?></option>
                                <option value="Vaticano"><?= $pago['vaticano']; ?></option>
                            </select>
                        </div>
                    </div>

                </div>

            </div>

            <div class="col-12 col-md-5 offset-md-1" style="background-color:#D8D6D6;padding:25px">
                <div class="container sticky-md-top">
                    <h3><?php echo $pago['detallesReserva']; ?></h3>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Productos</th>
                                <th scope="col">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <?php echo $pago['detallesReserva']; ?>
                                    <ul>
                                        <li><strong><?php echo $pago['tipoReserva']; ?></strong> <span id="tipoReserva"></span></li>
                                        <li><strong><?php echo $pago['fechaEntrada']; ?></strong> <span id="fechaEntrada"></span> / <span id="horaEntrada"></span></li>
                                        <li><strong><?php echo $pago['fechaSalida']; ?></strong> <span id="fechaSalida"></span> / <span id="horaSalida"></span></li>
                                        <li><strong><?php echo $pago['diasReserva']; ?></strong> <span id="diasReserva"></span> <?php echo $pago['dias']; ?></li>
                                    </ul>
                                </td>
                                <td>
                                    <p><span id="precioReserva"></span> <?php echo $pago['sinIva']; ?>
                                </td>
                            </tr>

                            <tr>
                                <td><strong><?php echo $pago['seguroCancelacion']; ?> </strong> <span id="seguroCancelacion"></span></td>
                                <td><span id="costeSeguro2"></span></td>
                            </tr>

                            <tr>
                                <td><strong><?php echo $pago['limpieza']; ?> </strong> <span id="tipoLimpieza2"></span></td>
                                <td><span id="costeLimpieza"></span></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $pago['subtotal']; ?></th>
                                <td><strong><span id="costeSubTotal"></span> € <?php echo $pago['sinIva']; ?></strong></td>
                            </tr>

                            <tr>
                                <th scope="row"><?php echo $pago['iva']; ?></th>
                                <td><strong><span id="costeIva"></span> €</strong></td>
                            </tr>

                            <tr>
                                <th scope="row"><?php echo $pago['total']; ?></th>
                                <td><strong><span id="costeTotal"></span> €</strong></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Formulario para ingresar la información del pago -->
                    <form name="form" action="" method="POST">
                        <!-- Casilla de verificación de términos y condiciones -->
                        <hr>
                        <p><?php echo $pago['datosPersonales']; ?></p>
                        <label>
                            <input type="checkbox" id="terminos_condiciones" name="terminos_condiciones" required><a href="/terminos-y-condiciones/" target="_blank">
                                <?php echo $pago['terminos']; ?></a>
                        </label>

                        <!-- Mensaje oculto de alerta -->
                        <div id="aviso_terminos" style="color: red; display: none;">
                            <?php echo $pago['aceptacionTerminos']; ?>
                        </div>

                        <!-- Botón de pagar dentro de un div oculto -->
                        <div id="div_pagar">
                            <button type="button" id="pagamentTargeta" class="payButton" style="margin-top:25px">
                                <strong><?php echo $pago['pagoTarjeta']; ?> <span id="costeTotal2"></span> €</strong>
                            </button>

                            <button type="button" id="pagamentBizum" class="payButton" style="margin-top:25px">
                                <strong><?php echo $pago['pagoBizum']; ?> <span id="costeTotal3"></span> €</strong>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>