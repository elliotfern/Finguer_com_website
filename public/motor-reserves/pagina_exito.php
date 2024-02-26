<script>
    function idReserva() {
        // Recuperar el valor de idReserva almacenado en el navegador
        let idReserva = sessionStorage.getItem('idReserva');
        let emailCliente = sessionStorage.getItem('email_cliente');
        let importe = sessionStorage.getItem('importe');
        let diaEntrada = sessionStorage.getItem('diaEntrada');
        let dias = sessionStorage.getItem('dias');

        // Llamada AJAX para actualizar la base de datos
        $.ajax({
            url: '/api/pago-ok-reserva', // Ruta a tu script PHP que actualiza la base de datos
            type: 'POST',
            data: { 
                idReserva: idReserva,
                emailCliente: emailCliente,
                importe: importe,
                diaEntrada: diaEntrada,
                dias: dias,
             },

            success: function(response) {
                // Aquí puedes manejar la respuesta del servidor si es necesario
            },
            error: function(xhr, status, error) {
                // Aquí puedes manejar errores si es necesario
            }
        });

    }
</script>
            
<?php
require_once(APP_ROOT . '/apiRedsys.php');

$token = $_ENV['MERCHANTCODE'];
$token2 = $_ENV['KEY'];
$token3 = $_ENV['TERMINAL'];

// Se crea Objeto
$miObj = new RedsysAPI;

if (!empty( $_POST ) ) {//URL DE RESP. ONLINE
                
                $version = $_POST["Ds_SignatureVersion"];
                $datos = $_POST["Ds_MerchantParameters"];
                $signatureRecibida = $_POST["Ds_Signature"];
                

                $decodec = $miObj->decodeMerchantParameters($datos);	
                $kc = $token2; //Clave recuperada de CANALES
                $firma = $miObj->createMerchantSignatureNotif($kc,$datos);	

                if ($firma === $signatureRecibida){
                  echo "OK";
                } else {
                    echo "FIRMA KO";
                }
} else {
    if (!empty( $_GET ) ) {//URL DE RESP. ONLINE
            
        $version = $_GET["Ds_SignatureVersion"];
        $datos = $_GET["Ds_MerchantParameters"];
        $signatureRecibida = $_GET["Ds_Signature"];
            
    
        $decodec = $miObj->decodeMerchantParameters($datos);
        $kc = $token2; //Clave recuperada de CANALES
        $firma = $miObj->createMerchantSignatureNotif($kc,$datos);
    
        if ($firma === $signatureRecibida) {
            // Llamar a la función JavaScript aquí
            echo '<script>idReserva();</script>';

            ?>
            <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title text-center mb-4">¡Compra Exitosa!</h3>
                                    <p class="card-text text-center">Su compra se ha procesado con éxito.</p>
                                    <p class="card-text text-center">Se ha enviado un correo electrónico con los detalles de la transacción.</p>
                                    <div class="text-center">
                                        <a href="/" class="btn btn-primary">Volver al inicio</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        <?php
        } else {
            echo "FIRMA KO";
        }
    }
    else{
        die("No se recibió respuesta");
    }
}

// Footer
require_once(APP_ROOT . '/public/footer_html.php');