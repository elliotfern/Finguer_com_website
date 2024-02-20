<?php
// Incluir la biblioteca de Stripe
require_once('vendor/autoload.php');

// Configurar la clave secreta de Stripe
\Stripe\Stripe::setApiKey('sk_test_Bk1pGDUuypMNPyWSQeRYkTos');

// Obtener el ID del método de pago y el precio total del formulario
$payment_method_id = $_POST['payment_method_id'];
$precio_total = $_POST['precio_total'];

try {
    // Crear el pago en Stripe
    $paymentIntent = \Stripe\PaymentIntent::create([
        'payment_method' => $payment_method_id,
        'amount' => $precio_total * 100, // La cantidad debe estar en centavos
        'currency' => 'eur', // Cambia a tu moneda preferida si es diferente
        'confirmation_method' => 'manual',
        'confirm' => true,
        'return_url' => 'http://localhost/botiga/pagina_de_retorno.php', // Reemplaza esto con la URL de tu página de retorno
    ]);

    // El pago se ha procesado correctamente
    $response = [
        'success' => true,
        'message' => 'El pago se ha procesado correctamente.',
        'payment_intent_id' => $paymentIntent->id,
    ];
    echo json_encode($response);
} catch (\Stripe\Exception\CardException $e) {
    // El pago ha sido rechazado por Stripe
    $response = [
        'success' => false,
        'message' => 'El pago ha sido rechazado. Por favor, verifica los detalles de tu tarjeta e intenta nuevamente.',
    ];
    echo json_encode($response);
} catch (\Exception $e) {
    // Otro tipo de error
    $response = [
        'success' => false,
        'message' => 'Ha ocurrido un error al procesar el pago. Por favor, intenta nuevamente más tarde.',
    ];
    echo json_encode($response);
}
?>
