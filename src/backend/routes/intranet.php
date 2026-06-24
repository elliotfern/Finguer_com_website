<?php

// Rutas principales sin idioma explícito (solo para el idioma por defecto)
$routes = [
    // INTRANET - AREA PRIVADA REQUEREIX TOKEN DE AUTENTICACIO
    '/control/login' => [
        'view' => './intranet/auth/login.php',
        'needs_session' => false,
    ],

    '/control' => [
        'view' => './intranet/1_reserves_pendents.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/reserves-pendents' => [
        'view' => './intranet/1_reserves_pendents.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],

    '/control/reserves-parking' => [
        'view' => './intranet/2_reserves_parking.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/reserves-completades' => [
        'view' => './intranet/3_reserves_completades.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],

    '/control/reserva/verificar-pagament/{id}' => [
        'view' => './intranet/soap/verificar-pagament.php',
        'needs_session' => true,
        'roles' => ['admin'],
    ],

    '/control/reserva/modificar/tipus/{id}' => [
        'view' => './intranet/form-modificar/tipus-reserva.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/reserva/modificar/telefon/{id}' => [
        'view' => './intranet/form-modificar/client-telefon.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/reserva/modificar/nom/{id}' => [
        'view' => './intranet/form-modificar/client-nom.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/reserva/modificar/entrada/{id}' => [
        'view' => './intranet/form-modificar/reserva-entrada.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/reserva/modificar/sortida/{id}' => [
        'view' => './intranet/form-modificar/reserva-sortida.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/reserva/modificar/vehicle/{id}' => [
        'view' => './intranet/form-modificar/vehicle.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/reserva/modificar/vol/{id}' => [
        'view' => './intranet/form-modificar/vol.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/reserva/modificar/nota/{id}' => [
        'view' => './intranet/form-modificar/nota.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/reserva/modificar/cercador/{id}' => [
        'view' => './intranet/form-modificar/cercador.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/reserva/modificar/reserva/{id}' => [
        'view' => './intranet/form-modificar/reserva.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/reserva/fer/check-in/{id}' => [
        'view' => './intranet/form-modificar/checkin.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/reserva/fer/check-out/{id}' => [
        'view' => './intranet/form-modificar/checkout.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],

    '/control/reserva/eliminar/reserva/{id}' => [
        'view' => './intranet/form-eliminar/reserva.php',
        'needs_session' => true,
        'roles' => ['admin'],
    ],

    '/control/reserva/info/nota/{id}' => [
        'view' => './intranet/form-info/nota.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/reserva/info/reserva/{id}' => [
        'view' => './intranet/form-info/reserva.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],

    // RESERVES ANUALS
    '/control/clients-anuals' => [
        'view' => './intranet/clients-anuals/clients.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],

    '/control/clients-anuals/pendents' => [
        'view' => './intranet/clients-anuals/estat-pendent.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/clients-anuals/parking' => [
        'view' => './intranet/clients-anuals/estat-parking.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/clients-anuals/completades' => [
        'view' => './intranet/clients-anuals/estat-completades.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],

    '/control/clients-anuals/nou-client' => [
        'view' => './intranet/clients-anuals/form-client.php',
        'needs_session' => true,
        'roles' => ['admin'],
    ],

    '/control/clients-anuals/modifica-client/{idClient}' => [
        'view' => './intranet/clients-anuals/form-client.php',
        'needs_session' => true,
        'roles' => ['admin'],
    ],

    '/control/clients-anuals/eliminar-client/{idClient}' => [
        'view' => './intranet/clients-anuals/eliminar-client.php',
        'needs_session' => true,
        'roles' => ['admin'],
    ],

    '/control/clients-anuals/nova-reserva' => [
        'view' => './intranet/clients-anuals/form-reserva-anual.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/clients-anuals/modifica-reserva/{idClient}' => [
        'view' => './intranet/clients-anuals/form-reserva-anual.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],

    // ALTRES
    '/control/cercador-reserva' => [
        'view' => './intranet/motor-cerca/cercador.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],

    '/control/calendari/entrades' => [
        'view' => './intranet/calendari-reserves/entrades.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/calendari/entrades/any/{any}/mes/{mes}' => [
        'view' => './intranet/calendari-reserves/entrades-mes.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/calendari/entrades/any/{any}/mes/{mes}/dia/{dia}' => [
        'view' => './intranet/calendari-reserves/entrades-dia.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],

    '/control/calendari/sortides' => [
        'view' => './intranet/calendari-reserves/sortides.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/calendari/sortides/any/{any}/mes/{mes}' => [
        'view' => './intranet/calendari-reserves/sortides-mes.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
    '/control/calendari/sortides/any/{any}/mes/{mes}/dia/{dia}' => [
        'view' => './intranet/calendari-reserves/sortides-dia.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],

    '/control/reserves' => [
        'view' => './intranet/reserves/llistat-reserves.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],

    // test
    '/control/test' => [
        'view' => './intranet/test/test.php',
        'needs_session' => true,
        'roles' => ['admin'],
    ],

    // Facturacio
    '/control/facturacio' => [
        'view' => './intranet/facturacio/index.php',
        'needs_session' => true,
        'roles' => ['admin'],
    ],

    // Control clients / usuaris
    '/control/usuaris' => [
        'view' => './intranet/gestio-clients/llistat-clients.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],

    '/control/usuaris/alta-client' => [
        'view' => './intranet/gestio-clients/form-client.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],

    '/control/usuaris/modifica-client/{id}' => [
        'view' => './intranet/gestio-clients/form-client.php',
        'needs_session' => true,
        'roles' => ['admin'],
    ],

    '/control/usuaris/reserves-client' => [
        'view' => './intranet/gestio-clients/llistat-reserves-client.php',
        'needs_session' => true,
        'roles' => ['admin', 'trabajador'],
    ],
];

return $routes;
