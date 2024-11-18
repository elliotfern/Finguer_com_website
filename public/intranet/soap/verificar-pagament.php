<?php

use RedsysConsultasPHP\Client\Client;

global $conn;

function getProtectedPropertyValue($object, $propertyName) {
    $reflection = new ReflectionClass($object);
    $property = $reflection->getProperty($propertyName);
    $property->setAccessible(true);
    return $property->getValue($object);
}

$id = $routeParams[0];

if (is_numeric($id)) {
    $id_old = intval($id);
    
    if (filter_var($id_old, FILTER_VALIDATE_INT)) {
        $codi_resposta = 2;

        // Consulta general reservas
        $sql = "SELECT r.idReserva
                FROM reserves_parking AS r
                WHERE r.id = :id";
        
        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->bindParam(':id', $id_old, PDO::PARAM_INT);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();

        if (empty($result)) {
            echo "<div class='alert alert-danger text-center' role='alert'>
                    <p>No s'ha trobat cap reserva amb aquest ID.</p>
                  </div>";
            return; // Termina la ejecución si no hay resultados
        }

        $idReserva = $result[0]['idReserva'];

        echo "<div class='container'>
                <h2>Verificar el pagament de la reserva: ".$idReserva."</h2>";

        $token = $_ENV['MERCHANTCODE'];
        $token2 = $_ENV['KEY'];
        $token3 = $_ENV['TERMINAL'];

        $url = 'https://sis.redsys.es/apl02/services/SerClsWSConsulta';
        $client = new Client($url, $token2);

        $order = $idReserva;
        $terminal = '1';
        $merchant_code = $token;

        try {
            $response = $client->getTransaction($order, $terminal, $merchant_code);

            if (!$response) {
                throw new Exception("No s'ha obtingut cap resposta de la API de RedSys.");
            }

            // Acceder a las propiedades
            $ds_response = getProtectedPropertyValue($response, 'Ds_Response');

            // Verificar el valor de Ds_Response
            switch ($ds_response) {
                case '9218':
                    echo "<div class='alert alert-danger text-center' role='alert'>
                            <p><img src='".APP_WEB."/inc/img/warning.png' alt='Pagament Error'></p>
                            <p><strong>Pagament fallit</strong>.</p>
                          </div>";
                    break;

                case '0000':
                    echo "<div class='alert alert-success text-center' role='alert'>
                            <p><img src='".APP_WEB."/inc/img/correct.png' alt='Pagament OK'></p>
                            <p><strong>Pagament verificat correctament amb RedSys.</strong></p>
                          </div>";

                    // Cambiar el estado del pago en la base de datos
                    $processed = 1;
                    $sql = "UPDATE reserves_parking SET processed = :processed WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":processed", $processed, PDO::PARAM_INT);
                    $stmt->bindParam(":id", $id_old, PDO::PARAM_INT);
                    $stmt->execute();
                    break;

                default:
                    echo "<div class='alert alert-danger text-center' role='alert'>
                            <p><img src='".APP_WEB."/inc/img/warning.png' alt='Pagament Error'></p>
                            <p><strong>No s'ha pogut verificar aquest pagament. Pagament fallit o denegat amb RedSys.</strong></p>
                          </div>";
                    break;
            }
        } catch (Exception $e) {
            // Manejar el error de la API de Redsys
            echo "<div class='alert alert-danger text-center' role='alert'>
                    <p><img src='".APP_WEB."/inc/img/warning.png' alt='Pagament Error'></p>
                    <p><strong>Error de pagament: " . htmlspecialchars($e->getMessage()) . "</strong></p>";
                        if ($e->getMessage() === 'Error XML0024') {
                            // Mostrar mensaje para el error específico
                            echo "<p><strong>Missatge de Redsys: No existen operaciones para los datos solicitados.</strong></p>";
                        }
                    "</div>";    
        }

    } else {
        echo "Error: aquest ID no és vàlid";
    }
} else {
    echo "Error. No has seleccionat cap reserva.";
}

echo '<a href="'.APP_WEB.'/inici" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
echo "</div>";