<?php

$token = $_ENV['MERCHANTCODE'];
$token2 = $_ENV['KEY'];
$token3 = $_ENV['TERMINAL'];

$url_Ok = $_ENV['URLOK'];
$url_Ko = $_ENV['URLKO'];

// Recibir los datos enviados por POST
$precio_reserva_sin_limpieza = isset($_POST['precioReservaSinLimpieza']) ? $_POST['precioReservaSinLimpieza'] : 0;
$tipoReserva = isset($_POST['tipoReserva']) ? $_POST['tipoReserva'] : '';
$fechaEntrada = isset($_POST['fechaEntrada']) ? $_POST['fechaEntrada'] : '';
$fechaSalida = isset($_POST['fechaSalida']) ? $_POST['fechaSalida'] : '';
$tipoLimpieza = isset($_POST['limpieza']) ? $_POST['limpieza'] : 0;
$numDias = isset($_POST['numDias']) ? $_POST['numDias'] : 0;

// Obtener el precio total de la reserva desde la URL
    if ($tipoReserva === "finguer_class") {
        $tipoReserva2 = "Finguer Class";
        $codigoTipoReserva = 1;
    } else {
        $tipoReserva2 = "Finguer Gold Class";
        $codigoTipoReserva = 2;
    }

    if ($tipoLimpieza == 0) {
        $tipoLimpieza2 = 0;
        $precioLimpieza = 0;
        $codigoLimpieza = 0;
    } elseif ($tipoLimpieza === "15") {
        $tipoLimpieza2 = "Servicio de limpieza exterior";
        $precioLimpieza = 15;
        $codigoLimpieza = 1;
    } elseif ($tipoLimpieza === "25") {
        $tipoLimpieza2 = "Servicio de lavado exterior + aspirado tapicería interior";
        $precioLimpieza = 25;
        $codigoLimpieza = 2;
    } elseif ($tipoLimpieza === "55") {
        $tipoLimpieza2 = "Lavado PRO. Lo dejamos como nuevo";
        $precioLimpieza = 55;
        $codigoLimpieza = 3;
    }

    // Transformar la fecha al formato UNIX
    $fechaUnix1 = strtotime($fechaEntrada);
    $fechaUnix2 = strtotime($fechaSalida);

    // Crear la fecha en el formato deseado "YYYY-D-M"
    $fechaEntrada2 = date('Y-j-m', $fechaUnix1);
    $fechaSalida2 = date('Y-j-m', $fechaUnix2);

    //  Calcula los precios -->
    $porcentaje_iva = 21;
    
    // 1 - Calcula el precio de la reserva sin IVA
    $reserva_sin_iva = $precio_reserva_sin_limpieza /1.21;

    // 2- Calcula el precio de la limpieza sin IVA
    $limpieza_sin_iva = $precioLimpieza / 1.21;

    // 3 - Calcula el subtotal
    $subtotal = $reserva_sin_iva + $limpieza_sin_iva;

    // 4 - Calcula el IVA total 21%
    $coste_iva = $subtotal * 0.21;
    
    // 5 - Calcula el Importe total iva incluido
    $importe_total = $subtotal + $coste_iva;
    
    // OBJECTE REDSYS
        $miObj = new RedsysAPI;

        // Valores de entrada que no hemos cmbiado para ningun ejemplo
        $fuc=$token;
        $terminal="1";
        $moneda="978";
        $trans="0";
        $url="";
        $urlOK= $url_Ok;
        $urlKO= $url_Ko;
        $id=time();
        $amount=$importe_total * 100;
        $payment="z";

        // Se Rellenan los campos
        $miObj->setParameter("DS_MERCHANT_AMOUNT",$amount);
        $miObj->setParameter("DS_MERCHANT_ORDER",$id);
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
        $miObj2->setParameter("DS_MERCHANT_PAYMETHODS",$payment);
        $miObj2->setParameter("DS_MERCHANT_AMOUNT",$amount);
        $miObj2->setParameter("DS_MERCHANT_ORDER",$id);
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
            <h4 class="alert-heading"><strong>Todo OK</h4></strong>
            <h6>Datos guardados</h6>
            </div>
                
            <div class="alert alert-danger" id="messageErr" style="display:none" role="alert">
            <h4 class="alert-heading"><strong>¡Error!</h4></strong>
            <h6>Por favor revise los datos que ha introducido antes de completar el pedido.</h6>
            </div>

            <h3>Datos de cliente</h3>
                <div class="col-md-6">
                <label for="nombre">Nombre y apellidos *</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>

                <div class="col-md-6">
                <label for="email">Dirección de correo electrónico *</label>
                <input type="text" class="form-control" id="email" name="email" required>
                </div>

                <div class="col-md-6">
                <label for="telefono">Teléfono *</label>
                <input type="text" class="form-control" id="telefono" name="telefono" required>
                </div>

                <div class="row g-3">
                <br>
                <h3>Datos de su vehículo y del vuelo</h3>
                <div class="col-md-6">
                <label for="modelo_vehiculo">Modelo vehículo *</label>
                <input type="text" class="form-control" id="vehiculo" name="vehiculo">
                </div>

                <div class="col-md-6">
                <label for="matricula">Matrícula vehículo*</label>
                <input type="text" class="form-control" id="matricula" name="matricula">
                </div>

                <div class="col-md-6">
                <label for="vuelo_retorno">Número vuelo retorno (opcional)</label>
                <input type="text" class="form-control" id="vuelo" name="vuelo">
                </div>

                </div>

            <div class="row g-3">
                <div class="col-md-6">
                <label for="horario_entrada">Horario entrada párking * </label>
                <select class="form-select" id="horaEntrada" name="horaEntrada">
                <option selected>Selecciona una hora:</option>
                    <option value="05:00">05:00</option>
                    <option value="05:30">05:30</option>
                    <option value="06:00">06:00</option>
                    <option value="06:30">06:30</option>
                    <option value="07:00">07:00</option>
                    <option value="07:30">07:30</option>
                    <option value="08:00">08:00</option>
                    <option value="08:30">08:30</option>
                    <option value="09:00">09:00</option>
                    <option value="09:30">09:30</option>
                    <option value="10:00">10:00</option>
                    <option value="10:30">10:30</option>
                    <option value="11:00">11:00</option>
                    <option value="11:30">11:30</option>
                    <option value="12:00">12:00</option>
                    <option value="12:30">12:30</option>
                    <option value="13:00">13:00</option>
                    <option value="13:30">13:30</option>
                    <option value="14:00">14:00</option>
                    <option value="14:30">14:30</option>
                    <option value="15:00">15:00</option>
                    <option value="15:30">15:30</option>
                    <option value="16:00">16:00</option>
                    <option value="16:30">16:30</option>
                    <option value="17:00">17:00</option>
                    <option value="17:30">17:30</option>
                    <option value="18:00">18:00</option>
                    <option value="18:30">18:30</option>
                    <option value="19:00">19:00</option>
                    <option value="19:30">19:30</option>
                    <option value="20:00">20:00</option>
                    <option value="20:30">20:30</option>
                    <option value="21:00">21:00</option>
                    <option value="21:30">21:30</option>
                    <option value="22:00">22:00</option>
                    <option value="22:30">22:30</option>
                    <option value="23:00">23:00</option>
                    <option value="23:30">23:30</option>
                </select>
                </div>

                <div class="col-md-6">
                <label for="horaSalida">Horario salida párking * </label>
                <select class="form-select" id="horaSalida" name="horaSalida">
                <option selected>Selecciona una hora:</option>
                    <option value="05:00">05:00</option>
                    <option value="05:30">05:30</option>
                    <option value="06:00">06:00</option>
                    <option value="06:30">06:30</option>
                    <option value="07:00">07:00</option>
                    <option value="07:30">07:30</option>
                    <option value="08:00">08:00</option>
                    <option value="08:30">08:30</option>
                    <option value="09:00">09:00</option>
                    <option value="09:30">09:30</option>
                    <option value="10:00">10:00</option>
                    <option value="10:30">10:30</option>
                    <option value="11:00">11:00</option>
                    <option value="11:30">11:30</option>
                    <option value="12:00">12:00</option>
                    <option value="12:30">12:30</option>
                    <option value="13:00">13:00</option>
                    <option value="13:30">13:30</option>
                    <option value="14:00">14:00</option>
                    <option value="14:30">14:30</option>
                    <option value="15:00">15:00</option>
                    <option value="15:30">15:30</option>
                    <option value="16:00">16:00</option>
                    <option value="16:30">16:30</option>
                    <option value="17:00">17:00</option>
                    <option value="17:30">17:30</option>
                    <option value="18:00">18:00</option>
                    <option value="18:30">18:30</option>
                    <option value="19:00">19:00</option>
                    <option value="19:30">19:30</option>
                    <option value="20:00">20:00</option>
                    <option value="20:30">20:30</option>
                    <option value="21:00">21:00</option>
                    <option value="21:30">21:30</option>
                    <option value="22:00">22:00</option>
                    <option value="22:30">22:30</option>
                    <option value="23:00">23:00</option>
                    <option value="23:30">23:30</option>
                </select>
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
                    <li><strong>Fecha de Entrada:</strong> <?php echo $fechaEntrada; ?></li>
                    <li><strong>Fecha de Salida:</strong> <?php echo $fechaSalida; ?></li>
                    <li><strong>Duración de la reserva:</strong> <?php echo $numDias; ?> días</li>
                </ul>
                </td>
                <td>
                <p> <?php echo number_format($reserva_sin_iva , 2, ',', '.'); ?> € (sin IVA);
            </td>
            </tr>

                <?php
                if ($precioLimpieza !== 0) {
                    ?> 
                    <tr>
                    <td><strong>Limpieza: </strong> <?php echo $tipoLimpieza2 ?></td>
                    <td><?php echo number_format($limpieza_sin_iva, 2, ',', '.'); ?> € (sin IVA)</td>
                    </tr>
                    <?php
                } else {
                
                }
                ?> 
                    <tr>
                    <th scope="row">Subtotal</th>
                    <td><strong><?php echo number_format($subtotal, 2, ',', '.'); ?> € (sin IVA)</strong></td>
                    </tr>

                    <tr>
                    <th scope="row">IVA (21%)</th>
                    <td><strong><?php echo number_format($coste_iva, 2, ',', '.'); ?> €</strong></td>
                    </tr>

                    <tr>
                    <th scope="row">Total</th>
                    <td><strong><?php echo number_format($importe_total, 2, ',', '.'); ?> €</strong></td>
                    </tr>
        </tbody>
        </table>

        <!-- Formulario para ingresar la información del pago -->
        <form name="frm" action="" method="POST">
        <input type="hidden" id="importe" name="importe" value="<?php echo $importe_total; ?>">

            <!-- Un contenedor donde se mostrarán mensajes de error o éxito de Stripe -->
            <div id="card-errors" role="alert"></div>

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
                <button id="pagar" style="margin-top:25px">
                    <strong>PAGO SEGURO CON TARJETA <?php echo number_format($importe_total, 2, ',', '.'); ?> €</strong>
                </button>

                <button id="pago_bizum" style="margin-top:25px">
                    <strong>PAGO SEGURO CON BIZUM <?php echo number_format($importe_total, 2, ',', '.'); ?> €</strong>
                </button>
            </div>
        </form>
        </div>
        </div>
    </div>
 </div>

 <script>
    $(document).ready(function() {
        // Función para actualizar el estado del botón de pagar y mostrar el aviso
        function actualizarBotonPagar() {
            if ($('#terminos_condiciones').is(':checked')) {
                $('#div_pagar button').prop('disabled', false); // Habilitar el botón de pagar
                $('#aviso_terminos').hide(); // Ocultar el aviso
            } else {
                $('#div_pagar button').prop('disabled', true); // Deshabilitar el botón de pagar
                $('#aviso_terminos').show(); // Mostrar el aviso
            }
        }

        // Llamar a la función cuando se carga la página
        actualizarBotonPagar();

        // Llamar a la función cada vez que se cambie el estado de la casilla de verificación
        $('#terminos_condiciones').change(function() {
            actualizarBotonPagar();
        });


        // funciones principales al clicar en el boton de pagar
        $('#pagar').click(function(event) {
            // Evitar que el formulario se envíe de forma tradicional
            event.preventDefault();

            // Declarar la variable nuevoClienteID fuera de la función success
            let nuevoClienteID;

            // Obtener los datos del formulario cliente
            let formData = {
                nombre: $("#nombre").val(),
                telefono: $("#telefono").val(),
                email: $("#email").val(),
                empresa: $("#empresa").val(),
                nif: $("#nif").val(),
                direccion: $("#direccion").val(),
                ciudad: $("#ciudad").val(),
                codigo_postal: $("#codigo_postal").val(),
                pais: $("#pais").val()
            };

            // Enviar los datos por AJAX para guardarlos en la tabla1
            $.ajax({
                url: '/api/alta-client',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status == "success") {

                        // Add response in Modal body
                        //$("#messageOk").show();
                        $("#messageErr").hide();

                        // Manejar el ID del nuevo cliente
                        nuevoClienteID = response.idCliente;

                         // Ahora puedes enviar los datos junto con el ID del nuevo cliente a guardar_datos_tabla2.php
                        let numeroAleatorio = Math.floor(1000 + Math.random() * 9000);

                        // Almacenar el número aleatorio en la sesión del navegador
                        sessionStorage.setItem('idReserva', numeroAleatorio);

                        // y tambien el correo electronico del cliente:
                        sessionStorage.setItem('email_cliente', $("#email").val());

                        $.ajax({
                                url: '/api/alta-reserva',
                                type: 'POST',
                                data: {
                                    idClient: nuevoClienteID,
                                    idReserva: numeroAleatorio,
                                    tipo: "<?php echo $codigoTipoReserva; ?>",
                                    horaEntrada: $("#horaEntrada").val(),
                                    diaEntrada: "<?php echo $fechaEntrada2; ?>",
                                    horaSalida: $("#horaSalida").val(),
                                    diaSalida: "<?php echo $fechaSalida2; ?>",
                                    vehiculo: $("#vehiculo").val(),
                                    matricula: $("#matricula").val(),
                                    vuelo: $("#vuelo").val(),
                                    limpieza: "<?php echo $codigoLimpieza; ?>",
                                    processed: "0",
                                    importe: $("#importe").val(),
                                },
                                success: function(response) {
                                    // Manejar la respuesta si es necesario
                                    console.log(response);

                                    if (response.status == "success") {

                                    // Redireccionar a la pasarela de pago de Redsys
                                    // Crear un formulario dinámicamente
                                    let form = $('<form action="https://sis.redsys.es/sis/realizarPago" method="post"></form>');
                                    
                                    // Agregar las variables como campos ocultos al formulario
                                    form.append('<input type="hidden" name="Ds_SignatureVersion" value="<?php echo $version; ?>">');
                                    form.append('<input type="hidden" name="Ds_MerchantParameters" value="<?php echo $params; ?>">');
                                    form.append('<input type="hidden" name="Ds_Signature" value="<?php echo $signature; ?>">');
                                    
                                    // Adjuntar el formulario al cuerpo del documento y enviarlo
                                    $('body').append(form);
                                    form.submit();
                                    } else {
                                        $("#messageOk").hide();
                                        $("#messageErr").show();
                                    }
                                },
                                error: function(xhr, status, error) {
                                    // Manejar errores si es necesario
                                    console.error(xhr, status, error);
                                }
                            });
                    } else if (response.status == "error") {
                        $("#messageOk").hide();
                        $("#messageErr").show();
                        // Manejar otro tipo de respuesta si es necesario
                        console.log(response);
                    }
                }
            });
        });
    });


    // PAGO CON BIZUM
    $('#pago_bizum').click(function(event) {
            // Evitar que el formulario se envíe de forma tradicional
            event.preventDefault();

            // Declarar la variable nuevoClienteID fuera de la función success
            let nuevoClienteID;

            // Obtener los datos del formulario cliente
            let formData = {
                nombre: $("#nombre").val(),
                telefono: $("#telefono").val(),
                email: $("#email").val(),
                empresa: $("#empresa").val(),
                nif: $("#nif").val(),
                direccion: $("#direccion").val(),
                ciudad: $("#ciudad").val(),
                codigo_postal: $("#codigo_postal").val(),
                pais: $("#pais").val()
            };

            // Enviar los datos por AJAX para guardarlos en la tabla1
            $.ajax({
                url: '/api/alta-client',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status == "success") {

                        // Add response in Modal body
                        //$("#messageOk").show();
                        $("#messageErr").hide();

                        // Manejar el ID del nuevo cliente
                        nuevoClienteID = response.idCliente;
                        console.log("ID del nuevo cliente:", nuevoClienteID);

                         // Ahora puedes enviar los datos junto con el ID del nuevo cliente a guardar_datos_tabla2.php
                        let numeroAleatorio = Math.floor(1000 + Math.random() * 9000);

                        // Almacenar el número aleatorio en la sesión del navegador
                        sessionStorage.setItem('idReserva', numeroAleatorio);
                        $.ajax({
                                url: '/api/alta-reserva',
                                type: 'POST',
                                data: {
                                    idClient: nuevoClienteID,
                                    idReserva: numeroAleatorio,
                                    tipo: "<?php echo $codigoTipoReserva; ?>",
                                    horaEntrada: $("#horaEntrada").val(),
                                    diaEntrada: "<?php echo $fechaEntrada2; ?>",
                                    horaSalida: $("#horaSalida").val(),
                                    diaSalida: "<?php echo $fechaSalida2; ?>",
                                    vehiculo: $("#vehiculo").val(),
                                    matricula: $("#matricula").val(),
                                    vuelo: $("#vuelo").val(),
                                    limpieza: "<?php echo $codigoLimpieza; ?>",
                                    processed: "0", // quité la coma extra al final
                                },
                                success: function(response) {
                                    // Manejar la respuesta si es necesario
                                    console.log(response);

                                    if (response.status == "success") {

                                    // Redireccionar a la pasarela de pago de Redsys
                                    // Crear un formulario dinámicamente
                                    let form = $('<form action="https://sis.redsys.es/sis/realizarPago" method="post"></form>');
                                    
                                    // Agregar las variables como campos ocultos al formulario
                                    form.append('<input type="hidden" name="Ds_SignatureVersion" value="<?php echo $version; ?>">');
                                    form.append('<input type="hidden" name="Ds_MerchantParameters" value="<?php echo $params2; ?>">');
                                    form.append('<input type="hidden" name="Ds_Signature" value="<?php echo $signature2; ?>">');

                                    // Adjuntar el formulario al cuerpo del documento y enviarlo
                                    $('body').append(form);
                                    form.submit();
                                    } else {
                                        $("#messageOk").hide();
                                        $("#messageErr").show();
                                    }
                                },
                                error: function(xhr, status, error) {
                                    // Manejar errores si es necesario
                                    console.error(xhr, status, error);
                                }
                            });
                    } else if (response.status == "error") {
                        $("#messageOk").hide();
                        $("#messageErr").show();
                        // Manejar otro tipo de respuesta si es necesario
                        console.log(response);
                    }
                }
            });
        });

</script>

</div>

<?php
// Footer
require_once(APP_ROOT . '/public/footer_html.php');