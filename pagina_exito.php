<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'apiRedsys.php';
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$token = $_ENV['MERCHANTCODE2'];
$token2 = $_ENV['KEY2'];
$token3 = $_ENV['TERMINAL2'];

// Se crea Objeto
$miObj = new RedsysAPI;


if (!empty( $_POST ) ) {//URL DE RESP. ONLINE
                
                $version = $_POST["Ds_SignatureVersion"];
                $datos = $_POST["Ds_MerchantParameters"];
                $signatureRecibida = $_POST["Ds_Signature"];
                

                $decodec = $miObj->decodeMerchantParameters($datos);	
                $kc = $token2; //Clave recuperada de CANALES
                $firma = $miObj->createMerchantSignatureNotif($kc,$datos);	

                echo PHP_VERSION."<br/>";
                echo $firma."<br/>";
                echo $signatureRecibida."<br/>";
                if ($firma === $signatureRecibida){
                    echo "FIRMA OK - compra exit";
                
                } else {
                    echo "FIRMA KO";
                }
}
else{
    if (!empty( $_GET ) ) {//URL DE RESP. ONLINE
            
        $version = $_GET["Ds_SignatureVersion"];
        $datos = $_GET["Ds_MerchantParameters"];
        $signatureRecibida = $_GET["Ds_Signature"];
            
    
        $decodec = $miObj->decodeMerchantParameters($datos);
        $kc = $token2; //Clave recuperada de CANALES
        $firma = $miObj->createMerchantSignatureNotif($kc,$datos);
    
        if ($firma === $signatureRecibida){
            echo "FIRMA OK - compra exit";

            session_start();
            if (isset($_SESSION['nombre']) && isset($_SESSION['email'])) {
                $nombre = $_SESSION['nombre'];
                $email = $_SESSION['email'];
                // Procesar los datos como desees
                echo "Nombre: $nombre<br>";
                echo "Email: $email<br>";
                // Limpia la sesión
                session_unset();
                session_destroy();
            } else {
                echo "No se encontraron datos del formulario en la sesión.";
            }

        } else {
            echo "FIRMA KO";
        }
    }
    else{
        die("No se recibió respuesta");
    }
}

?>