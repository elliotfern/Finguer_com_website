<div class="container" id="pantallaPagamentError" style="display:none;margin-bottom:400px;margin-top:100px">
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">¡En este momento no tienes ninguna reserva pendiente de pago!</h4>
        <p>Por favor, vuelve a la página de inicio para realizar tu reserva.</p>
    </div>

    <!-- Botón para volver a la página de inicio -->
    <a href="/" class="btn btn-primary">Volver</a>
</div>

<div class="container-fluid" id="pantallaPagament" style="display:none">
    <div class="row">
        <div class="col-12 col-md-8" style="padding:25px">
            <div class="container quadre-formulari-pagina-pagament">

                <div class="row g-3">

                    <div class="alert alert-success" id="messageOk" style="display:none" role="alert">
                        <h4 class="alert-heading"><strong>Todo OK</strong></h4>
                        <h6>Datos guardados</h6>
                    </div>

                    <div class="alert alert-danger" id="messageErr" style="display:none" role="alert">
                        <h4 class="alert-heading"><strong>¡Error!</strong></h4>
                        <h6>Por favor revise los datos que ha introducido antes de completar el pedido.</h6>
                    </div>

                    <h3>Datos de cliente</h3>
                    <div class="col-md-6">
                        <label for="nombre">Nombre y apellidos *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                        <div class="invalid-feedback" id="error-nombre"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="email">Dirección de correo electrónico *</label>
                        <input type="text" class="form-control" id="email" name="email" required>
                        <div class="invalid-feedback" id="error-email"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="telefono">Teléfono *</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" required>
                        <div class="invalid-feedback" id="error-telefono"></div>
                    </div>

                    <div class="row g-3">
                        <br>
                        <h3>Datos de su vehículo y del vuelo</h3>
                        <div class="col-md-6">
                            <label for="modelo_vehiculo">Modelo vehículo *</label>
                            <input type="text" class="form-control" id="vehiculo" name="vehiculo">
                            <div id="error-vehiculo" class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="matricula">Matrícula vehículo*</label>
                            <input type="text" class="form-control" id="matricula" name="matricula">
                            <div id="error-matricula" class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="vuelo_retorno">Número vuelo retorno</label>
                            <input type="text" class="form-control" id="vuelo" name="vuelo">
                            <div id="error-vuelo" class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="numero_personas">Número de acompañantes</label>
                            <input type="number" class="form-control" id="numero_personas" name="numero_personas" min="1" max="100" required>
                            <div id="numero_personasHelp" class="form-text">*Si vais a ser más de 8 personas, por favor, avisad antes al párking.</div>
                            <div id="error-numero_personas" class="invalid-feedback"></div>
                        </div>

                    </div>

                    <h3>Datos de facturación (datos opcionales)</h3>
                    <div class="alert alert-dark" role="alert">
                        Sólo rellene estos datos si necesita la factura.
                    </div>
                    <div class="col-md-6">
                        <label for="empresa">Nombre de la empresa (opcional)</label>
                        <input type="text" class="form-control" id="empresa" name="empresa">
                    </div>

                    <div class="col-md-6">
                        <label for="nif">NIF/NIE/CIF (opcional):</label>
                        <input type="text" class="form-control" id="nif" name="nif">
                    </div>

                    <div class="col-md-6">
                        <label for="direccion">Dirección (opcional):</label>
                        <input type="text" class="form-control" id="direccion" name="direccion">
                    </div>

                    <div class="col-md-6">
                        <label for="ciudad">Localidad / Ciudad (opcional):</label>
                        <input type="text" class="form-control" id="ciudad" name="ciudad">
                    </div>

                    <div class="col-md-6">
                        <label for="codigo_postal">Código postal (opcional):</label>
                        <input type="text" class="form-control" id="codigo_postal" name="codigo_postal">
                    </div>

                    <div class="col-md-6">
                        <label for="pais" class="form-label">País (opcional):</label>
                        <select class="form-select" name="pais" id="pais">
                            <option value="">Seleccione un país</option>
                            <option value="España">España</option>
                            <option value="Albania">Albania</option>
                            <option value="Alemania">Alemania</option>
                            <option value="Andorra">Andorra</option>
                            <option value="Austria">Austria</option>
                            <option value="Bélgica">Bélgica</option>
                            <option value="Bielorrusia">Bielorrusia</option>
                            <option value="Bosnia y Herzegovina">Bosnia y Herzegovina</option>
                            <option value="Bulgaria">Bulgaria</option>
                            <option value="Chipre">Chipre</option>
                            <option value="Croacia">Croacia</option>
                            <option value="Dinamarca">Dinamarca</option>
                            <option value="Eslovaquia">Eslovaquia</option>
                            <option value="Eslovenia">Eslovenia</option>
                            <option value="Estonia">Estonia</option>
                            <option value="Finlandia">Finlandia</option>
                            <option value="Francia">Francia</option>
                            <option value="Grecia">Grecia</option>
                            <option value="Hungría">Hungría</option>
                            <option value="Irlanda">Irlanda</option>
                            <option value="Islandia">Islandia</option>
                            <option value="Italia">Italia</option>
                            <option value="Kosovo">Kosovo</option>
                            <option value="Letonia">Letonia</option>
                            <option value="Liechtenstein">Liechtenstein</option>
                            <option value="Lituania">Lituania</option>
                            <option value="Luxemburgo">Luxemburgo</option>
                            <option value="Malta">Malta</option>
                            <option value="Moldavia">Moldavia</option>
                            <option value="Mónaco">Mónaco</option>
                            <option value="Montenegro">Montenegro</option>
                            <option value="Noruega">Noruega</option>
                            <option value="Países Bajos">Países Bajos</option>
                            <option value="Polonia">Polonia</option>
                            <option value="Portugal">Portugal</option>
                            <option value="Reino Unido">Reino Unido</option>
                            <option value="República Checa">República Checa</option>
                            <option value="República de Macedonia del Norte">República de Macedonia del Norte</option>
                            <option value="Rumanía">Rumanía</option>
                            <option value="Rusia">Rusia</option>
                            <option value="San Marino">San Marino</option>
                            <option value="Serbia">Serbia</option>
                            <option value="Suecia">Suecia</option>
                            <option value="Suiza">Suiza</option>
                            <option value="Ucrania">Ucrania</option>
                            <option value="Vaticano">Vaticano</option>
                        </select>
                    </div>
                </div>

            </div>

        </div>

        <div class="col-12 col-md-4" style="background-color:#D8D6D6;padding:25px">
            <div class="container sticky-md-top">
                <h3>Detalles de la reserva</h3>

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
                                Detalles de la Reserva:
                                <ul>
                                    <li><strong>Tipo de Reserva:</strong> <span id="tipoReserva"></span></li>
                                    <li><strong>Fecha de Entrada:</strong> <span id="fechaEntrada"></span> / <span id="horaEntrada"></span></li>
                                    <li><strong>Fecha de Salida:</strong> <span id="fechaSalida"></span> / <span id="horaSalida"></span></li>
                                    <li><strong>Duración de la reserva:</strong> <span id="diasReserva"></span> días</li>
                                </ul>
                            </td>
                            <td>
                                <p><span id="precioReserva"></span> € (sin IVA);
                            </td>
                        </tr>

                        <tr>
                            <td><strong>Seguro de Cancelación: </strong> <span id="seguroCancelacion"></span></td>
                            <td><span id="costeSeguro2"></span></td>
                        </tr>

                        <tr>
                            <td><strong>Limpieza: </strong> <span id="tipoLimpieza2"></span></td>
                            <td><span id="costeLimpieza"></span></td>
                        </tr>
                        <tr>
                            <th scope="row">Subtotal</th>
                            <td><strong><span id="costeSubTotal"></span> € (sin IVA)</strong></td>
                        </tr>

                        <tr>
                            <th scope="row">IVA (21%)</th>
                            <td><strong><span id="costeIva"></span> €</strong></td>
                        </tr>

                        <tr>
                            <th scope="row">Total</th>
                            <td><strong><span id="costeTotal"></span> €</strong></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Formulario para ingresar la información del pago -->
                <form name="form" action="" method="POST">
                    <!-- Casilla de verificación de términos y condiciones -->
                    <hr>
                    <p>Tus datos personales se utilizarán para procesar tu reserva.</p>
                    <label>
                        <input type="checkbox" id="terminos_condiciones" name="terminos_condiciones" required>
                        He leído y estoy de acuerdo con los<a href="/terminos-y-condiciones/" target="_blank"> Términos y Condiciones de compra de la web. </a>
                    </label>

                    <!-- Mensaje oculto de alerta -->
                    <div id="aviso_terminos" style="color: red; display: none;">
                        * Debes aceptar los términos y condiciones antes de continuar con el pago.
                    </div>

                    <!-- Botón de pagar dentro de un div oculto -->
                    <div id="div_pagar">
                        <button type="button" id="pagamentTargeta" class="payButton" style="margin-top:25px">
                            <strong>PAGO SEGURO CON TARJETA <span id="costeTotal2"></span> €</strong>
                        </button>

                        <button type="button" id="pagamentBizum" class="payButton" style="margin-top:25px">
                            <strong>PAGO SEGURO CON BIZUM <span id="costeTotal3"></span> €</strong>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</div>