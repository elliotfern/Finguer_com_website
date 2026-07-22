<?php

// Rutas principales sin idioma explícito (solo para el idioma por defecto)
$routes = [
    // Dominio: Usuario
    '/api/usuaris/get' => [
        'view' =>
            '../src/backend/Infrastructure/EntryPoint/Http/Usuario/Endpoint/ListarUsuarioEndpoint.php',
        'no_header_footer' => true,
        'needs_session' => true,
    ],

    '/api/usuaris/alta-client' => [
        'view' =>
            '../src/backend/Infrastructure/EntryPoint/Http/Usuario/Endpoint/CrearUsuarioEndpoint.php',
        'no_header_footer' => true,
        'needs_session' => false,
    ],

    '/api/usuaris/post' => [
        'view' =>
            '../src/backend/Infrastructure/EntryPoint/Http/Usuario/Endpoint/CrearUsuarioEndpoint.php',
        'no_header_footer' => true,
        'needs_session' => true,
    ],

    '/api/usuaris/put' => [
        'view' =>
            '../src/backend/Infrastructure/EntryPoint/Http/Usuario/Endpoint/ActualizarUsuarioEndpoint.php',
        'no_header_footer' => true,
        'needs_session' => true,
    ],

    '/api/usuaris/login' => [
        'view' =>
            '../src/backend/Infrastructure/EntryPoint/Http/Usuario/Endpoint/LoginEndpoint.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],

    // Dominio: Reserva
    '/api/reserva/post/alta-reserva' => [
        'view' =>
            '../src/backend/Infrastructure/EntryPoint/Http/Reserva/Endpoint/CrearReservaEndpoint.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],

    '/api/area-client/reservas' => [
        'view' => '../src/backend/api/area-client/reservas.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],

    '/api/reserva/post/cancelar-reserva' => [
        'view' =>
            '../src/backend/Infrastructure/EntryPoint/Http/Reserva/Endpoint/CancelarReservaEndpoint.php',
        'needs_session' => true,
        'no_header_footer' => true,
    ],

    '/api/reserva/post/actualizar-estado-vehiculo' => [
        'view' =>
            '../src/backend/Infrastructure/EntryPoint/Http/Reserva/Endpoint/ActualizarEstadoVehiculoEndpoint.php',
        'needs_session' => true,
        'no_header_footer' => true,
    ],

    '/api/reserva/post/alta-reserva-anual' => [
        'view' =>
            '../src/backend/Infrastructure/EntryPoint/Http/Reserva/Endpoint/CrearReservaAnualEndpoint.php',
        'no_header_footer' => true,
        'needs_session' => true,
    ],

    '/api/reserva/put/actualizar-reserva-anual' => [
        'view' =>
            '../src/backend/Infrastructure/EntryPoint/Http/Reserva/Endpoint/ActualizarReservaAnualEndpoint.php',
        'no_header_footer' => true,
        'needs_session' => true,
    ],

    '/api/reserva/put/actualizar-reserva' => [
        'view' =>
            '../src/backend/Infrastructure/EntryPoint/Http/Reserva/Endpoint/ActualizarDatosReservaEndpoint.php',
        'no_header_footer' => true,
        'needs_session' => true,
    ],

    // Dominio: Catálogo
    '/api/catalogo/get/configuracion-reserva' => [
        'view' =>
            '../src/backend/Infrastructure/EntryPoint/Http/Catalogo/Endpoint/ConfiguracionReservaEndpoint.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],

    '/api/catalogo/get/horas-disponibles' => [
        'view' =>
            '../src/backend/Infrastructure/EntryPoint/Http/Catalogo/Endpoint/HorasDisponiblesEndpoint.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],

    // Dominio: Carrito
    '/api/carrito/get' => [
        'view' =>
            '../src/backend/Infrastructure/EntryPoint/Http/Carrito/Endpoint/ObtenerCarritoEndpoint.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],
    '/api/carrito/post' => [
        'view' =>
            '../src/backend/Infrastructure/EntryPoint/Http/Carrito/Endpoint/GuardarCarritoEndpoint.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],

    // Dominio: Pago
    '/api/pago/post/pagament-redsys-targeta' => [
        'view' =>
            '../src/backend/Infrastructure/EntryPoint/Http/Pago/Endpoint/PrepararPagoRedsysEndpoint.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],

    // altres

    '/api/formulario/post' => [
        'view' =>
            '../src/backend/api/web_publica/formulario/post-formulario.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],

    // API INTRANET

    '/api/intranet/reserves/get' => [
        'view' => '../src/backend/api/intranet/get-reserves.php',
        'needs_session' => true,
        'no_header_footer' => true,
    ],

    '/api/intranet/users/get' => [
        'view' => '../src/backend/api/intranet/get-users.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],

    '/api/intranet/email/get' => [
        'view' => '../src/backend/api/intranet/email/get-email.php',
        'needs_session' => true,
        'no_header_footer' => true,
    ],

    // API WEB PUBLICA

    // 1 - CARRO DE LA COMPRA

    '/api/pagamentRedsysBizum' => [
        'view' => '../src/backend/api/web_publica/pagament-redsys-bizum.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],

    '/api/notificacioRedsys' => [
        'view' => '../src/backend/api/web_publica/redsys-notificacio.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],

    // GESTIO CLIENTS I CLIENTS ANUALS
    '/api/clients/get/{slug}' => [
        'view' => '../src/backend/api/intranet/clients/get-clients.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],

    // API FACTURES
    '/api/factures/send' => [
        'view' => '../src/backend/api/intranet/factures/enviar-factura.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],
    '/api/factures/get' => [
        'view' => '../src/backend/api/intranet/factures/get-factura.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],
    '/api/factures/post' => [
        'view' => '../src/backend/api/intranet/factures/post-factura.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],
    '/api/factures/post/confirmar-pago-manual' => [
        'view' =>
            '../src/backend/api/intranet/factures/post-confirmar-pago-manual.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],

    // /api/factures/logs' => ['view' => '../src/backend/api/intranet/factures/logs.php', 'needs_session' => false, 'no_header_footer' => true],
    '/api/factures/hash' => [
        'view' => '../src/backend/api/intranet/factures/hash.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],

    // ALTRES
    '/api/uuid' => [
        'view' => '../src/backend/api/generarUUID.php',
        'no_header_footer' => true,
        'needs_session' => true,
    ],

    '/api/comprova' => [
        'view' => '../src/backend/api/comprova.php',
        'no_header_footer' => true,
        'needs_session' => false,
    ],
];

return $routes;
