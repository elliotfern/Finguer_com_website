<?php

require __DIR__ . '../../utils/utils.php';

// Combinar todas las rutas en un solo arreglo
$routes = array_merge(
    require __DIR__ . '/api.php',
    require __DIR__ . '/area_cliente.php',
    require __DIR__ . '/cron.php',
    require __DIR__ . '/intranet.php',
    require __DIR__ . '/web_publica.php'
);

return $routes;
