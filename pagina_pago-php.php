<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Pago</title>
    <!-- Agrega los scripts de Stripe y jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

 <!-- Agregar CryptoJS desde un CDN -->
 <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
</head>
<body>

    <!-- Mostrar el precio total -->
    
    <?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Creagia\Redsys\Enums\Currency;
use Creagia\Redsys\Enums\TransactionType;
use Creagia\Redsys\RedsysClient;
use Creagia\Redsys\RedsysRequest;
use Creagia\Redsys\Support\RequestParameters;

$token = $_ENV['MERCHANTCODE'];
$token2 = $_ENV['KEY'];
$token3 = $_ENV['TERMINAL'];

$redsysClient = new RedsysClient(
    merchantCode: $token,
    secretKey: $token2,
    terminal: $token3,
    environment: \Creagia\Redsys\Enums\Environment::Test,
);

$redsysRequest = RedsysRequest::create(
    $redsysClient,
    new RequestParameters(
        amountInCents: 12345,
        orderNumber: '22013100005',
        currency: Currency::EUR,
        transactionType: TransactionType::Autorizacion,
        merchantUrl: 'https://example.com/redsysNotification',
        urlOk: 'https://example.com/paymentOk',
        urlKo: 'https://example.com/paymentKo',
    )
);

echo $redsysRequest->getRedirectFormHtml();

    // Obtener el precio total de la reserva desde la URL
    $costo_total = isset($_GET['precio_total']) ? $_GET['precio_total'] : 0;
    $precio_reserva_sin_limpieza = isset($_GET['precio_reserva_sin_limpieza']) ? $_GET['precio_reserva_sin_limpieza'] : 0;
    

    $tipoReserva = isset($_GET['tipo_reserva']) ? $_GET['tipo_reserva'] : '';
    
    if ($tipoReserva === "finguer_class") {
        $tipoReserva2 = "Finguer Class";
    } else {
        $tipoReserva2 = "Finguer Gold Class";
    }

    $tipoLimpieza = isset($_GET['limpieza']) ? $_GET['limpieza'] : '';
    if ($tipoLimpieza === "0") {
        $tipoLimpieza2 = "";
        $precioLimpieza = 0;
    } elseif ($tipoLimpieza === "15") {
        $tipoLimpieza2 = "Servicio de limpieza exterior";
        $precioLimpieza = 15;
    } elseif ($tipoLimpieza === "25") {
        $tipoLimpieza2 = "Servicio de lavado exterior + aspirado tapicería interior";
        $precioLimpieza = 25;
    } elseif ($tipoLimpieza === "55") {
        $tipoLimpieza2 = "Lavado PRO. Lo dejamos como nuevo";
        $precioLimpieza = 55;
    }
    
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
    
    ?>

<style>
    #payment-form {
        max-width: 400px;
        margin: 0 auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 10px;
        background-color: #f9f9f9;
    }

    label {
        font-weight: bold;
        margin-bottom: 5px;
        display: block;
    }

    input[type="text"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
    }

    #card-element {
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #fff;
    }

    #card-errors {
        color: #dc3545;
        margin-bottom: 20px;
    }

    button {
        display: block;
        width: 100%;
        padding: 10px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #0056b3;
    }
</style>

    <div class="container-fluid">
  <div class="row">
    <div class="col-8">
        <div class="container" style="width:60%">
            <h3>Datos de facturación</h3>
            <div class="row g-3">
                <div class="col-md-6">
                <label for="nombre">Nombre y apellidos *</label>
                <input type="text" class="form-control" id="card-holder-name" name="card-holder-name" required>
                </div>

                <div class="col-md-6">
                <label for="empresa">Nombre de la empresa (opcional)</label>
                <input type="text" class="form-control" id="empresa" name="empresa">
                </div>

                <div class="col-md-6">
                <label for="nif">NIF/NIE/CIF (opcional)</label>
                <input type="text" class="form-control" id="nif" name="nif">
                </div>

                <div class="col-md-6">
                <label for="direccion">Dirección (opcional)</label>
                <input type="text" class="form-control" id="direccion" name="direccion">
                </div>

                <div class="col-md-6">
                <label for="ciudad">Localidad / Ciudad (opcional)</label>
                <input type="text" class="form-control" id="ciudad" name="ciudad">
                </div>

                <div class="col-md-6">
                <label for="codigo_postal">Código postal (opcional)</label>
                <input type="text" class="form-control" id="codigo_postal" name="codigo_postal">
                </div>

                <div class="col-md-6">
                <label for="pais">País *</label>
                <input type="text" class="form-control" id="pais" name="pais" required>
                </div>

                <div class="col-md-6">
                <label for="telefono">Teléfono *</label>
                <input type="tel" class="form-control" id="telefono" name="telefono" required>
                </div>

                <div class="col-md-6">
                <label for="email">Dirección de correo electrónico *</label>
                <input type="email" class="form-control" id="email" name="email" required>
                </div>

            </div>

            <div class="row g-3">
                <br>
                <h3>Datos del vehiculo y del vuelo</h3>
                <div class="col-md-6">
                <label for="modelo_vehiculo">Modelo vehículo *</label>
                <input type="text" class="form-control" id="modelo_vehiculo" name="modelo_vehiculo" required>
                </div>

                <div class="col-md-6">
                <label for="matricula">Matrícula *</label>
                <input type="text" class="form-control" id="matricula" name="matricula" required>
                </div>

                <div class="col-md-6">
                <label for="vuelo_retorno">Número vuelo retorno (opcional)</label>
                <input type="text" class="form-control" id="vuelo_retorno" name="vuelo_retorno">
                </div>
                
                <div class="col-md-6">
                <label for="horario_entrada">Horario entrada párking * </label>
                <input type="time" class="form-control" id="horario_entrada" name="horario_entrada" required>
                </div>
                
                <div class="col-md-6">
                <label for="horario_salida">Horario de salida párking *</label>
                <input type="time" class="form-control" id="horario_salida" name="horario_salida" required>
                </div>

            </div>
        </div>

    </div>
    
    <div class="col-4" style="background-color:#D8D6D6">
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
                    <li><strong>Tipo de Reserva:</strong> <?php echo $tipoReserva2 ?></li>
                    <li><strong>Fecha de Entrada:</strong> <?php echo isset($_GET['fecha_entrada']) ? $_GET['fecha_entrada'] : ''; ?></li>
                    <li><strong>Fecha de Salida:</strong> <?php echo isset($_GET['fecha_salida']) ? $_GET['fecha_salida'] : ''; ?></li>
                    <li><strong>Duración de la reserva:</strong> <?php echo isset($_GET['diasReserva']) ? $_GET['diasReserva'] : ''; ?> días</li>
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
        <form id="payment-form">
            <label for="card-element">
                Ingresa los detalles de tu tarjeta de crédito:
            </label>
            <div id="card-element">
                <!-- Un elemento de Stripe para recopilar la información de la tarjeta -->
            </div>

            <!-- Un contenedor donde se mostrarán mensajes de error o éxito de Stripe -->
            <div id="card-errors" role="alert"></div>

            <button  id="pay-button">
                <strong>Pagar <?php echo number_format($importe_total, 2, ',', '.'); ?> €</strong>
            </button>
        </form>
        </div>
        </div>
    </div>
 </div>


<!-- Mensaje de procesamiento oculto -->
<div id="processing-message" style="display: none;">
    <p>Procesando su pago...</p>
</div>

<!-- Mensaje de éxito oculto -->
<div id="success-message" style="display: none;">
    <p>¡El pago se ha realizado con éxito!</p>
</div>

<!-- Mensaje de error oculto -->
<div id="error-message" style="display: none;">
    <p>Ocurrió un error al procesar su pago. Por favor, inténtelo de nuevo más tarde.</p>
</div>




</div>
</body>
</html>
