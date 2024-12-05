<?php

// Rutas de la aplicación con sus respectivas vistas y si requieren sesión o no
$routes = [
    // 01. Home
    '/' => ['view' => 'public/index.php', 'needs_session' => false],
    '/fr' => ['view' => 'public/index.php', 'needs_session' => false],
    '/en' => ['view' => 'public/index.php', 'needs_session' => false],

    // 02. Motor reserves
    '/pago' => ['view' => 'public/motor-reserves/pagina_pago.php', 'needs_session' => false],
    '/fr/pago' => ['view' => 'public/motor-reserves/pagina_pago.php', 'needs_session' => false],
    '/en/pago' => ['view' => 'public/motor-reserves/pagina_pago.php', 'needs_session' => false],

    // 03. Post redirecciones redsys
    '/compra-realizada' => ['view' => 'public/motor-reserves/pagina_exito.php', 'needs_session' => false],
    '/error-compra' => ['view' => 'public/motor-reserves/pagina_exito2.php', 'needs_session' => false],

    // 04. Legal
    '/politica-de-privacidad-finguer' => ['view' => 'public/politica-privacidad.php', 'needs_session' => false],
    '/terminos-y-condiciones' => ['view' => 'public/terminos-condiciones.php', 'needs_session' => false],

    // 05. Area cliente - necesita verificacion
    '/area-cliente/login' => ['view' => 'public/area-client/login.php', 'needs_session' => false, 'needs_verification' => false, 'no_header_footer' => false],
    '/area-cliente' => ['view' => 'public/area-client/index.php', 'needs_session' => false, 'needs_verification' => true, 'no_header_footer' => false],
    '/area-cliente/validar-token' => ['view' => 'public/area-client/validar-token.php', 'needs_session' => false, 'needs_verification' => false, 'no_header_footer' => true],
    '/area-cliente/reservas' => ['view' => 'public/area-client/index.php', 'needs_session' => false, 'needs_verification' => true, 'no_header_footer' => false],

    // API AREA CLIENT
    '/api/area-client/login' => ['view' => 'api/area-client/authClient.php', 'needs_session' => false, 'no_header_footer' => true],
    '/api/area-client/reservas' => ['view' => 'api/area-client/reservas.php', 'needs_session' => false, 'no_header_footer' => true],

    // API INTRANET
    '/api/intranet/auth/login' => ['view' => 'api/intranet/auth/login.php', 'needs_session' => false, 'no_header_footer' => true],
    '/api/intranet/reserves/get' => ['view' => 'api/intranet/get-reserves.php', 'needs_session' => true, 'no_header_footer' => true],
    '/api/intranet/reserves/post' => ['view' => 'api/intranet/post-login.php', 'needs_session' => true, 'no_header_footer' => true],
    '/api/intranet/users/get' => ['view' => 'api/intranet/get-users.php', 'needs_session' => true, 'no_header_footer' => true],

    '/api/intranet/email/get' => ['view' => 'api/intranet/email/get-email.php', 'needs_session' => true, 'no_header_footer' => true],

    // API WEB PUBLICA
    '/api/alta-client' => ['view' => 'api/crear-usuario.php', 'needs_session' => false, 'no_header_footer' => true],
    '/api/alta-reserva' => ['view' => 'api/crear-reserva.php', 'needs_session' => false, 'no_header_footer' => true],
    '/api/pago-ok-reserva' => ['view' => 'api/cmodificar-reserva-pago-ok.php', 'needs_session' => false, 'no_header_footer' => true],

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

    '/control/clients-anuals' => ['view' => 'public/intranet/clients-anuals/clients.php', 'needs_session' => true],

    '/control/clients-anuals/pendents' => ['view' => 'public/intranet/clients-anuals/estat-pendent.php', 'needs_session' => true],
    '/control/clients-anuals/parking' => ['view' => 'public/intranet/clients-anuals/estat-parking.php', 'needs_session' => true],
    '/control/clients-anuals/completades' => ['view' => 'public/intranet/clients-anuals/estat-completades.php', 'needs_session' => true],

    '/control/clients-anuals/modificar/client/{idClient}' => ['view' => 'public/intranet/clients-anuals/modificar-client.php', 'needs_session' => true],
    '/control/clients-anuals/eliminar/client/{idClient}' => ['view' => 'public/intranet/clients-anuals/eliminar-client.php', 'needs_session' => true],

    '/control/clients-anuals/crear/reserva/' => ['view' => 'public/intranet/clients-anuals/crear-reserva.php', 'needs_session' => true],
    '/control/clients-anuals/crear/reserva/{idClient}' => ['view' => 'public/intranet/clients-anuals/crear-reserva.php', 'needs_session' => true],
    '/control/clients-anuals/crear/client' => ['view' => 'public/intranet/clients-anuals/crear-client.php', 'needs_session' => true],

    '/control/cercador-reserva' => ['view' => 'public/intranet/motor-cerca/cercador.php', 'needs_session' => true],

    '/control/calendari/entrades' => ['view' => 'public/intranet/calendari-reserves/entrades.php', 'needs_session' => true],
    '/control/calendari/entrades/any/{any}/mes/{mes}' => ['view' => 'public/intranet/calendari-reserves/entrades-mes.php', 'needs_session' => true],
    '/control/calendari/entrades/any/{any}/mes/{mes}/dia/{dia}' => ['view' => 'public/intranet/calendari-reserves/entrades-dia.php', 'needs_session' => true],

    '/control/calendari/sortides' => ['view' => 'public/intranet/calendari-reserves/sortides.php', 'needs_session' => true],
    '/control/calendari/sortides/any/{any}/mes/{mes}' => ['view' => 'public/intranet/calendari-reserves/sortides-mes.php', 'needs_session' => true],
    '/control/calendari/sortides/any/{any}/mes/{mes}/dia/{dia}' => ['view' => 'public/intranet/calendari-reserves/sortides-dia.php', 'needs_session' => true],

    '/control/reserves' => ['view' => 'public/intranet/reserves/llistat-reserves.php', 'needs_session' => true],

    // TREBALLS CRON RESERVES - SENSE SESSIO PRIVADA
    '/cron/reserves' => ['view' => 'public/cron/cron-reserves.php', 'needs_session' => false],
    '/cron/pagats' => ['view' => 'public/cron/cron-pagats.php', 'needs_session' => false],
    
];