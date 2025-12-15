<?php

// Define las rutas base que quieres traducir
$base_routes = [
    '/control/login' => 'public/intranet/auth/login.php',
    '/control' => 'public/intranet/1_reserves_pendents.php',
    '/control/reserves-pendents' => 'public/intranet/1_reserves_pendents.php',
    '/control/reserves-parking' => 'public/intranet/2_reserves_parking.php',
    '/control/reserves-completades' => 'public/intranet/3_reserves_completades.php',
    '/control/reserva/verificar-pagament/{id}' => 'public/intranet/soap/verificar-pagament.php',
    '/control/reserva/modificar/tipus/{id}' => 'public/intranet/form-modificar/tipus-reserva.php',
    '/control/reserva/modificar/telefon/{id}' => 'public/intranet/form-modificar/client-telefon.php',
    '/control/reserva/modificar/nom/{id}' => 'public/intranet/form-modificar/client-nom.php',
    '/control/reserva/modificar/entrada/{id}' => 'public/intranet/form-modificar/reserva-entrada.php',
    '/control/reserva/modificar/sortida/{id}' => 'public/intranet/form-modificar/reserva-sortida.php',
    '/control/reserva/modificar/vehicle/{id}' => 'public/intranet/form-modificar/vehicle.php',
    '/control/reserva/modificar/vol/{id}' => 'public/intranet/form-modificar/vol.php',
    '/control/reserva/modificar/nota/{id}' => 'public/intranet/form-modificar/nota.php',
    '/control/reserva/modificar/cercador/{id}' => 'public/intranet/form-modificar/cercador.php',
    '/control/reserva/modificar/reserva/{id}' => 'public/intranet/form-modificar/reserva.php',
    '/control/reserva/fer/check-in/{id}' => 'public/intranet/form-modificar/checkin.php',
    '/control/reserva/fer/check-out/{id}' => 'public/intranet/form-modificar/checkout.php',
    '/control/reserva/eliminar/reserva/{id}' => 'public/intranet/form-eliminar/reserva.php',
    '/control/reserva/info/nota/{id}' => 'public/intranet/form-info/nota.php',
    '/control/reserva/info/reserva/{id}' => 'public/intranet/form-info/reserva.php',
    '/control/reserva/email/confirmacio/{id}' => 'public/intranet/email/reserva-enviar-email.php',
    '/control/reserva/email/factura/{id}' => 'public/intranet/email/reserva-enviar-factura-pdf.php',

    // RESERVES ANUALS
    '/control/clients-anuals' => 'public/intranet/clients-anuals/clients.php',
    '/control/clients-anuals/pendents' => 'public/intranet/clients-anuals/estat-pendent.php',
    '/control/clients-anuals/parking' => 'public/intranet/clients-anuals/estat-parking.php',
    '/control/clients-anuals/completades' => 'public/intranet/clients-anuals/estat-completades.php',
    '/control/clients-anuals/modificar/client/{idClient}' => 'public/intranet/clients-anuals/modificar-client.php',
    '/control/clients-anuals/eliminar/client/{idClient}' => 'public/intranet/clients-anuals/eliminar-client.php',

    '/control/clients-anuals/crear/reserva/' => 'public/intranet/clients-anuals/crear-reserva.php',
    '/control/clients-anuals/crear/reserva/{idClient}' => 'public/intranet/clients-anuals/crear-reserva.php',

    '/control/clients-anuals/crear/client' => 'public/intranet/clients-anuals/crear-client.php',

    // 
    '/control/cercador-reserva' => 'public/intranet/motor-cerca/cercador.php',
    '/control/calendari/entrades' => 'public/intranet/calendari-reserves/entrades.php',
    '/control/calendari/entrades/any/{any}/mes/{mes}' => 'public/intranet/calendari-reserves/entrades-mes.php',
    '/control/calendari/entrades/any/{any}/mes/{mes}/dia/{dia}' => 'public/intranet/calendari-reserves/entrades-dia.php',
    '/control/calendari/sortides' => 'public/intranet/calendari-reserves/sortides.php',
    '/control/calendari/sortides/any/{any}/mes/{mes}' => 'public/intranet/calendari-reserves/sortides-mes.php',
    '/control/calendari/sortides/any/{any}/mes/{mes}/dia/{dia}' => 'public/intranet/calendari-reserves/sortides-dia.php',
    '/control/reserves' => 'public/intranet/reserves/llistat-reserves.php',

    // test
    '/control/test' => 'public/intranet/test/test.php',

    // Facturacio
    '/control/facturacio' => 'public/intranet/facturacio/index.php',
];

// Rutas principales sin idioma explícito (solo para el idioma por defecto)
$routes = [
    // INTRANET - AREA PRIVADA REQUEREIX TOKEN DE AUTENTICACIO
    '/control/login' => ['view' => 'public/intranet/auth/login.php', 'needs_session' => false],
    '/control' => ['view' => 'public/intranet/1_reserves_pendents.php', 'needs_session' => true],
    '/control/reserves-pendents' => ['view' => 'public/intranet/1_reserves_pendents.php', 'needs_session' => true],

    '/control/reserves-parking' => ['view' => 'public/intranet/2_reserves_parking.php', 'needs_session' => true],
    '/control/reserves-completades' => ['view' => 'public/intranet/3_reserves_completades.php', 'needs_session' => true],

    '/control/reserva/verificar-pagament/{id}' => ['view' => 'public/intranet/soap/verificar-pagament.php', 'needs_session' => true],

    '/control/reserva/modificar/tipus/{id}' => ['view' => 'public/intranet/form-modificar/tipus-reserva.php', 'needs_session' => true],
    '/control/reserva/modificar/telefon/{id}' => ['view' => 'public/intranet/form-modificar/client-telefon.php', 'needs_session' => true],
    '/control/reserva/modificar/nom/{id}' => ['view' => 'public/intranet/form-modificar/client-nom.php', 'needs_session' => true],
    '/control/reserva/modificar/entrada/{id}' => ['view' => 'public/intranet/form-modificar/reserva-entrada.php', 'needs_session' => true],
    '/control/reserva/modificar/sortida/{id}' => ['view' => 'public/intranet/form-modificar/reserva-sortida.php', 'needs_session' => true],
    '/control/reserva/modificar/vehicle/{id}' => ['view' => 'public/intranet/form-modificar/vehicle.php', 'needs_session' => true],
    '/control/reserva/modificar/vol/{id}' => ['view' => 'public/intranet/form-modificar/vol.php', 'needs_session' => true],
    '/control/reserva/modificar/nota/{id}' => ['view' => 'public/intranet/form-modificar/nota.php', 'needs_session' => true],
    '/control/reserva/modificar/cercador/{id}' => ['view' => 'public/intranet/form-modificar/cercador.php', 'needs_session' => true],
    '/control/reserva/modificar/reserva/{id}' => ['view' => 'public/intranet/form-modificar/reserva.php', 'needs_session' => true],
    '/control/reserva/fer/check-in/{id}' => ['view' => 'public/intranet/form-modificar/checkin.php', 'needs_session' => true],
    '/control/reserva/fer/check-out/{id}' => ['view' => 'public/intranet/form-modificar/checkout.php', 'needs_session' => true],

    '/control/reserva/eliminar/reserva/{id}' => ['view' => 'public/intranet/form-eliminar/reserva.php', 'needs_session' => true],

    '/control/reserva/info/nota/{id}' => ['view' => 'public/intranet/form-info/nota.php', 'needs_session' => true],
    '/control/reserva/info/reserva/{id}' => ['view' => 'public/intranet/form-info/reserva.php', 'needs_session' => true],

    '/control/reserva/email/confirmacio/{id}' => ['view' => 'public/intranet/email/reserva-enviar-email.php', 'needs_session' => true],
    '/control/reserva/email/factura/{id}' => ['view' => 'public/intranet/email/reserva-enviar-factura-pdf.php', 'needs_session' => true],

    // RESERVES ANUALS
    '/control/clients-anuals' => ['view' => 'public/intranet/clients-anuals/clients.php', 'needs_session' => true],

    '/control/clients-anuals/pendents' => ['view' => 'public/intranet/clients-anuals/estat-pendent.php', 'needs_session' => true],
    '/control/clients-anuals/parking' => ['view' => 'public/intranet/clients-anuals/estat-parking.php', 'needs_session' => true],
    '/control/clients-anuals/completades' => ['view' => 'public/intranet/clients-anuals/estat-completades.php', 'needs_session' => true],

    '/control/clients-anuals/modificar/client/{idClient}' => ['view' => 'public/intranet/clients-anuals/modificar-client.php', 'needs_session' => true],
    '/control/clients-anuals/eliminar/client/{idClient}' => ['view' => 'public/intranet/clients-anuals/eliminar-client.php', 'needs_session' => true],

    '/control/clients-anuals/crear/reserva/' => ['view' => 'public/intranet/clients-anuals/crear-reserva.php', 'needs_session' => true],
    '/control/clients-anuals/crear/reserva/{idClient}' => ['view' => 'public/intranet/clients-anuals/crear-reserva.php', 'needs_session' => true],
    '/control/clients-anuals/crear/client' => ['view' => 'public/intranet/clients-anuals/crear-client.php', 'needs_session' => true],


    // ALTRES
    '/control/cercador-reserva' => ['view' => 'public/intranet/motor-cerca/cercador.php', 'needs_session' => true],

    '/control/calendari/entrades' => ['view' => 'public/intranet/calendari-reserves/entrades.php', 'needs_session' => true],
    '/control/calendari/entrades/any/{any}/mes/{mes}' => ['view' => 'public/intranet/calendari-reserves/entrades-mes.php', 'needs_session' => true],
    '/control/calendari/entrades/any/{any}/mes/{mes}/dia/{dia}' => ['view' => 'public/intranet/calendari-reserves/entrades-dia.php', 'needs_session' => true],

    '/control/calendari/sortides' => ['view' => 'public/intranet/calendari-reserves/sortides.php', 'needs_session' => true],
    '/control/calendari/sortides/any/{any}/mes/{mes}' => ['view' => 'public/intranet/calendari-reserves/sortides-mes.php', 'needs_session' => true],
    '/control/calendari/sortides/any/{any}/mes/{mes}/dia/{dia}' => ['view' => 'public/intranet/calendari-reserves/sortides-dia.php', 'needs_session' => true],

    '/control/reserves' => ['view' => 'public/intranet/reserves/llistat-reserves.php', 'needs_session' => true],

    // test
    '/control/test' => ['view' => 'public/intranet/test/test.php', 'needs_session' => true],

    // Facturacio
    '/control/facturacio' => ['view' => 'public/intranet/facturacio/index.php', 'needs_session' => true],
];

// Unir rutas base con rutas específicas de idioma
$routes = $routes + generateLanguageRoutes($base_routes, false);
return $routes;
