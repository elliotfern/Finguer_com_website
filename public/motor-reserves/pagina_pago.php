<?php
$token = $_ENV['MERCHANTCODE'];
$token2 = $_ENV['KEY'];
$token3 = $_ENV['TERMINAL'];

$url_Ok = $_ENV['URLOK'];
$url_Ko = $_ENV['URLKO'];


// Recibir los datos enviados por POST
$costeReserva = isset($_POST['costeReserva']) ? $_POST['costeReserva'] : 0;
$costeLimpieza = isset($_POST['costeLimpieza']) ? $_POST['costeLimpieza'] : 0;
$costeSubTotal = isset($_POST['costeSubTotal']) ? $_POST['costeSubTotal'] : 0;
$costeIva = isset($_POST['costeIva']) ? $_POST['costeIva'] : 0;
$costeTotal = isset($_POST['costeTotal']) ? $_POST['costeTotal'] : 0;
$costeSeguro = isset($_POST['costeSeguro']) ? $_POST['costeSeguro'] : 0;

$tipoReserva = isset($_POST['tipoReserva']) ? $_POST['tipoReserva'] : '';
$fechaEntrada = isset($_POST['fechaEntrada']) ? $_POST['fechaEntrada'] : '';
$fechaSalida = isset($_POST['fechaSalida']) ? $_POST['fechaSalida'] : '';
$tipoLimpieza = isset($_POST['limpieza']) ? $_POST['limpieza'] : 0;
$numDias = isset($_POST['numDias']) ? $_POST['numDias'] : 0;
$seguroCancelacion = isset($_POST['seguroCancelacion']) ? $_POST['seguroCancelacion'] : 0;

$horaEntrada = isset($_POST['horaEntrada']) ? $_POST['horaEntrada'] : 0;
$horaSalida = isset($_POST['horaSalida']) ? $_POST['horaSalida'] : 0;

// Obtener el precio total de la reserva desde la URL
    if ($tipoReserva === "finguer_class") {
        $tipoReserva2 = "Finguer Class";
        $codigoTipoReserva = 1;
    } else {
        $tipoReserva2 = "Finguer Gold Class";
        $codigoTipoReserva = 2;
    }

    if ($tipoLimpieza == 0) {
        $tipoLimpieza2 = "No contratado";
        $codigoLimpieza = 0;
    } elseif ($tipoLimpieza === "15") {
        $tipoLimpieza2 = "Servicio de limpieza exterior";
        $codigoLimpieza = 1;
    } elseif ($tipoLimpieza === "25") {
        $tipoLimpieza2 = "Servicio de lavado exterior + aspirado tapicería interior";
        $codigoLimpieza = 2;
    } elseif ($tipoLimpieza === "55") {
        $tipoLimpieza2 = "Lavado PRO. Lo dejamos como nuevo";
        $codigoLimpieza = 3;
    }

    if ($seguroCancelacion === "1") {
        $cancelacion = "Sí";
    } else {
        $cancelacion = "No";
    }

    // Transformar la fecha al formato UNIX
    $fechaUnix1 = strtotime($fechaEntrada);
    $fechaUnix2 = strtotime($fechaSalida);

    // Crear la fecha en el formato deseado "YYYY-D-M"
    $fechaEntrada2 = date('Y-j-m', $fechaUnix1);
    $fechaSalida2 = date('Y-j-m', $fechaUnix2);
      
    // OBJECTE REDSYS
        $miObj = new RedsysAPI;

        // Valores de entrada
        $fuc = $token;
        $terminal = $token3;
        $moneda = "978";
        $trans = "0";
        $url = "";
        $urlOK = $url_Ok;
        $urlKO = $url_Ko;
        $idReserva = date("mdHis");
        $amount = round($costeTotal * 100);

        // Se Rellenan los campos
        $miObj->setParameter("DS_MERCHANT_AMOUNT",$amount);
        $miObj->setParameter("DS_MERCHANT_ORDER",$idReserva);
        $miObj->setParameter("DS_MERCHANT_MERCHANTCODE",$fuc);
        $miObj->setParameter("DS_MERCHANT_CURRENCY",$moneda);
        $miObj->setParameter("DS_MERCHANT_TRANSACTIONTYPE",$trans);
        $miObj->setParameter("DS_MERCHANT_TERMINAL",$terminal);
        $miObj->setParameter("DS_MERCHANT_MERCHANTURL",$url);
        $miObj->setParameter("DS_MERCHANT_URLOK",$urlOK);
        $miObj->setParameter("DS_MERCHANT_URLKO",$urlKO);


        //Datos de configuración
        $version="HMAC_SHA256_V1";
        $kc = $token2;//Clave recuperada de CANALES

        // Se generan los parámetros de la petición
        $request = "";
        $params = $miObj->createMerchantParameters();
        $signature = $miObj->createMerchantSignature($kc);

        //para bizum
        $miObj2 = new RedsysAPI;

        // Se Rellenan los campos
        $payment = "z";

        $miObj2->setParameter("DS_MERCHANT_PAYMETHODS",$payment);
        $miObj2->setParameter("DS_MERCHANT_AMOUNT",$amount);
        $miObj2->setParameter("DS_MERCHANT_ORDER",$idReserva);
        $miObj2->setParameter("DS_MERCHANT_MERCHANTCODE",$fuc);
        $miObj2->setParameter("DS_MERCHANT_CURRENCY",$moneda);
        $miObj2->setParameter("DS_MERCHANT_TRANSACTIONTYPE",$trans);
        $miObj2->setParameter("DS_MERCHANT_TERMINAL",$terminal);
        $miObj2->setParameter("DS_MERCHANT_MERCHANTURL",$url);
        $miObj2->setParameter("DS_MERCHANT_URLOK",$urlOK);
        $miObj2->setParameter("DS_MERCHANT_URLKO",$urlKO);

        $params2 = $miObj2->createMerchantParameters();
        $signature2 = $miObj2->createMerchantSignature($kc);
    ?>

   <div class="container-fluid">
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

            <input type="hidden" id="idOrder" name="idOrder" value="<?php echo $id;?>">
            
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
                    <li><strong>Tipo de Reserva:</strong> <?php echo $tipoReserva2; ?></li>
                    <li><strong>Fecha de Entrada:</strong> <?php echo $fechaEntrada; ?> / <?php echo $horaEntrada; ?></li>
                    <li><strong>Fecha de Salida:</strong> <?php echo $fechaSalida; ?> / <?php echo $horaSalida; ?></li>
                    <li><strong>Duración de la reserva:</strong> <?php echo $numDias; ?> días</li>
                </ul>
                </td>
                <td>
                <p> <?php echo number_format($costeReserva , 2, ',', '.'); ?> € (sin IVA);
            </td>
            </tr>

            <tr>
                <td><strong>Seguro de Cancelación: </strong> <?php echo $cancelacion; ?></td>
                <td><?php echo number_format($costeSeguro, 2, ',', '.'); ?> € (sin IVA)</td>
            </tr>

                <?php
                if ($costeLimpieza !== 0) {
                    ?> 
                    <tr>
                    <td><strong>Limpieza: </strong> <?php echo $tipoLimpieza2 ?></td>
                    <td><?php echo number_format($costeLimpieza, 2, ',', '.'); ?> € (sin IVA)</td>
                    </tr>
                    <?php
                } else {
                
                }
                ?> 
                    <tr>
                    <th scope="row">Subtotal</th>
                    <td><strong><?php echo number_format($costeSubTotal, 2, ',', '.'); ?> € (sin IVA)</strong></td>
                    </tr>

                    <tr>
                    <th scope="row">IVA (21%)</th>
                    <td><strong><?php echo number_format($costeIva, 2, ',', '.'); ?> €</strong></td>
                    </tr>

                    <tr>
                    <th scope="row">Total</th>
                    <td><strong><?php echo number_format($costeTotal, 2, ',', '.'); ?> €</strong></td>
                    </tr>
        </tbody>
        </table>

        <!-- Formulario para ingresar la información del pago -->
        <form name="form" action="" method="POST">
        <input type="hidden" id="importe" name="importe" value="<?php echo $costeTotal; ?>">
        <input type="hidden" id="cancelacion" name="cancelacion" value="<?php echo $seguroCancelacion; ?>">
        <input type="hidden" id="tipo" name="tipo" value="<?php echo $codigoTipoReserva; ?>">
        <input type="hidden" id="horaEntrada" name="horaEntrada" value="<?php echo $horaEntrada; ?>">
        <input type="hidden" id="diaEntrada" name="diaEntrada" value="<?php echo $fechaEntrada2; ?>">
        <input type="hidden" id="horaSalida" name="horaSalida" value="<?php echo $horaSalida; ?>">
        <input type="hidden" id="diaSalida" name="diaSalida" value="<?php echo $fechaSalida2; ?>">
        <input type="hidden" id="limpieza" name="limpieza" value="<?php echo $codigoLimpieza; ?>">
        <input type="hidden" id="cancelacion" name="cancelacion" value="<?php echo $seguroCancelacion; ?>">
        <input type="hidden" id="costeSeguro" name="costeSeguro" value="<?php echo $costeSeguro; ?>">
        <input type="hidden" id="costeReserva" name="costeReserva" value="<?php echo $costeReserva; ?>">
        <input type="hidden" id="costeLimpieza" name="costeLimpieza" value="<?php echo $costeLimpieza; ?>">
        <input type="hidden" id="costeSubTotal" name="costeSubTotal" value="<?php echo $costeSubTotal; ?>">
        <input type="hidden" id="costeIva" name="costeIva" value="<?php echo $costeIva; ?>">
        <input type="hidden" id="costeTotal" name="costeTotal" value="<?php echo $costeTotal; ?>">

        <input type="hidden" id="version" name="version" value="<?php echo $version; ?>">
        <input type="hidden" id="params2" name="params2" value="<?php echo $params2; ?>">
        <input type="hidden" id="signature2" name="signature2" value="<?php echo $signature2; ?>">

        <input type="hidden" id="params" name="params" value="<?php echo $params; ?>">
        <input type="hidden" id="signature" name="signature" value="<?php echo $signature; ?>">
        <input type="hidden" id="idReserva" name="idReserva" value="<?php echo $idReserva; ?>">
        
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
                <button type="button" id="pagamentTargeta" style="margin-top:25px">
                    <strong>PAGO SEGURO CON TARJETA <?php echo number_format($costeTotal, 2, ',', '.'); ?> €</strong>
                </button>

                <button type="button" id="pagamentBizum" style="margin-top:25px">
                    <strong>PAGO SEGURO CON BIZUM <?php echo number_format($costeTotal, 2, ',', '.'); ?> €</strong>
                </button>
            </div>
        </form>
        </div>
        </div>
    </div>
 </div>

    </div>