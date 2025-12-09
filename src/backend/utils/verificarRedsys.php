<?php

use RedsysConsultasPHP\Client\Client;


/**
 * Helper para acceder a propiedades protegidas de un objeto.
 */
if (!function_exists('getProtectedPropertyValue')) {
    function getProtectedPropertyValue($object, $propertyName)
    {
        // Si no es un objeto, no podemos leer nada
        if (!is_object($object)) {
            return null;
        }

        $reflection = new ReflectionClass($object);

        if (!$reflection->hasProperty($propertyName)) {
            return null;
        }

        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}

function verificarPagamentRedsys($id, $ejecutarAcciones = false)
{
    if (!is_numeric($id)) {
        return [
            'status'  => 'error',
            'message' => 'No se ha seleccionado ninguna reserva.'
        ];
    }

    $idReservaNueva = (int)$id;

    if (!filter_var($idReservaNueva, FILTER_VALIDATE_INT)) {
        return [
            'status'  => 'error',
            'message' => 'ID no válido.'
        ];
    }

    global $conn;

    // 1) Buscar la reserva en la BD NUEVA
    $sql = "
        SELECT 
            pr.id,
            pr.localizador,
            pr.usuario_id
        FROM epgylzqu_parking_finguer_v2.parking_reservas pr
        WHERE pr.id = :id
        LIMIT 1
    ";

    /** @var PDO $conn */
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $idReservaNueva, PDO::PARAM_INT);
    $stmt->execute();
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        return [
            'status'  => 'error',
            'message' => 'No se encontró la reserva en la nueva base de datos.'
        ];
    }

    $localizador = $reserva['localizador']; // Ds_Order que mandamos a Redsys

    // 2) Llamar a Redsys (servicio de consultas)
    $merchantCode = $_ENV['MERCHANTCODE'] ?? null;
    $merchantKey  = $_ENV['KEY'] ?? null;

    if (!$merchantCode || !$merchantKey) {
        return [
            'status'  => 'error',
            'message' => 'Error de configuración Redsys: faltan credenciales.'
        ];
    }

    $url    = 'https://sis.redsys.es/apl02/services/SerClsWSConsulta';
    $client = new Client($url, $merchantKey);

    $order    = $localizador; // Ds_Order
    $terminal = '1';

    try {
        $response = $client->getTransaction($order, $terminal, $merchantCode);

        // Si no hay objeto, la librería nos indica que no hay operación
        if (!is_object($response)) {
            // Aquí es muy típico el caso de operaciones antiguas (ej. octubre)
            // que ya no están disponibles en el servicio de consultas.
            return [
                'status'  => 'error',
                'message' => 'Redsys: no existen operaciones para los datos solicitados (puede ser una operación antigua).'
            ];
        }

        $ds_response = getProtectedPropertyValue($response, 'Ds_Response');

        if ($ds_response === null) {
            return [
                'status'  => 'error',
                'message' => 'Error Redsys: no se pudo leer el código de respuesta.'
            ];
        }

        switch ($ds_response) {
            case '9218':
                return [
                    'status'  => 'error',
                    'message' => 'Error 9218 Redsys: Pago no procesado.',
                ];

            case '0000':
                // Pago OK
                $data = [
                    'status'  => 'success',
                    'message' => 'Redsys: Pago verificado correctamente.',
                ];

                if ($ejecutarAcciones) {
                    // 3) Acciones en BD nueva: marcar pago + reserva
                    try {
                        $conn->beginTransaction();

                        // 3.3 Marcar reserva como pagada
                        $sqlUpdReserva = "
                            UPDATE epgylzqu_parking_finguer_v2.parking_reservas
                            SET estado = 'pagada'
                            WHERE id = :id
                        ";
                        $stmtUpd = $conn->prepare($sqlUpdReserva);
                        $stmtUpd->bindParam(':id', $idReservaNueva, PDO::PARAM_INT);
                        $stmtUpd->execute();

                        // ⚠️ Futuro:
                        // - crear factura + facturas_lineas
                        // - actualizar pagos.factura_id
                        $conn->commit();
                    } catch (\Throwable $e) {
                        if ($conn->inTransaction()) {
                            $conn->rollBack();
                        }
                        return [
                            'status'  => 'error',
                            'message' => 'Error al actualizar el pago en la base de datos.',
                        ];
                    }
                }

                // En cualquier caso, devolvemos éxito
                return $data;

            default:
                return [
                    'status'  => 'error',
                    'message' => 'Error Redsys: No se ha podido verificar el pago (código ' . $ds_response . ').',
                ];
        }
    } catch (\Exception $e) {

        if ($e->getMessage() === 'Error XML0024') {
            return [
                'status'  => 'error',
                'message' => 'Error XML0024 Redsys: No existen operaciones para los datos solicitados.'
            ];
        }

        // Log opcional:
        // error_log('Error Redsys: ' . $e->getMessage());

        return [
            'status'  => 'error',
            'message' => 'Error Redsys'
        ];
    }
}
