<?php

use RedsysConsultasPHP\Client\Client;

function consultaRedsys(string $order): array
{
    $order = trim($order);
    if ($order === '') {
        return vp2_err('Order (localizador) vacío.', 'REDSYS_ORDER_EMPTY');
    }

    $merchantCode = (string)($_ENV['MERCHANTCODE'] ?? '');
    $key          = (string)($_ENV['KEY'] ?? '');
    $url          = 'https://sis.redsys.es/apl02/services/SerClsWSConsulta';
    $terminal     = '1';

    if ($merchantCode === '' || $key === '') {
        return vp2_err('Configuración Redsys incompleta (MERCHANTCODE/KEY).', 'REDSYS_CONFIG_MISSING');
    }

    // helper tuyo (si no existe, lo definimos)
    if (!function_exists('getProtectedPropertyValue')) {
        function getProtectedPropertyValue($object, $propertyName)
        {
            if (!is_object($object)) return null;
            $reflection = new ReflectionClass($object);
            if (!$reflection->hasProperty($propertyName)) return null;
            $property = $reflection->getProperty($propertyName);
            $property->setAccessible(true);
            return $property->getValue($object);
        }
    }

    try {
        $client   = new Client($url, $key);
        $response = $client->getTransaction($order, $terminal, $merchantCode);

        if (!is_object($response)) {
            return vp2_err('Redsys devolvió respuesta inválida.', 'REDSYS_INVALID_RESPONSE', [
                'data' => ['order' => $order]
            ]);
        }

        $ds = getProtectedPropertyValue($response, 'Ds_Response');
        if ($ds === null) {
            return vp2_err('No se pudo leer Ds_Response.', 'REDSYS_NO_DS_RESPONSE', [
                'data' => ['order' => $order]
            ]);
        }

        $ds = (string)$ds;

        // Interpretación simple
        // - 0000 => pago OK
        // - XML0024 suele venir como excepción, pero por si acaso...
        // - 9218 etc: errores típicos
        $paid = ($ds === '0000');

        $msg = $paid
            ? 'Redsys: pago verificado correctamente.'
            : "Redsys: pago no confirmado (Ds_Response={$ds}).";

        return vp2_ok($msg, [
            'redsys' => [
                'order'       => $order,
                'ds_response' => $ds,
                'paid'        => $paid,
                'terminal'    => $terminal,
            ],
        ]);
    } catch (\Throwable $e) {
        // Caso típico que ya contemplabas
        if ($e->getMessage() === 'Error XML0024') {
            return vp2_err(
                'Redsys: no existen operaciones para los datos solicitados (XML0024).',
                'REDSYS_XML0024',
                ['data' => ['order' => $order]]
            );
        }

        return vp2_err(
            'Excepción consultando Redsys.',
            'REDSYS_EXCEPTION',
            ['data' => ['order' => $order, 'error' => $e->getMessage()]]
        );
    }
}
