<?php

return [
    // API AREA CLIENT
    '/api/area-client/login' => ['view' => 'src/backend/api/area-client/authClient.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/area-client/reservas' => ['view' => 'src/backend/api/area-client/reservas.php', 'needs_session' => false, 'no_header_footer' => true],

    // API INTRANET
    '/api/intranet/auth/login' => ['view' => 'src/backend/api/intranet/auth/login.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/intranet/reserves/get' => ['view' => 'src/backend/api/intranet/get-reserves.php', 'needs_session' => true, 'no_header_footer' => true],

    '/api/intranet/reserves/post' => ['view' => 'src/backend/api/intranet/post-login.php', 'needs_session' => true, 'no_header_footer' => true],

    '/api/intranet/users/get' => ['view' => 'src/backend/api/intranet/get-users.php', 'needs_session' => true, 'no_header_footer' => true],

    '/api/intranet/email/get' => ['view' => 'src/backend/api/intranet/email/get-email.php', 'needs_session' => true, 'no_header_footer' => true],

    // API WEB PUBLICA
    '/api/alta-client' => ['view' => 'src/backend/api/web_publica/crear-usuario.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/alta-reserva' => ['view' => 'src/backend/api/web_publica/crear-reserva.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/pagamentRedsysTargeta' => ['view' => 'src/backend/api/web_publica/pagament-redsys-targeta.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/pagamentRedsysBizum' => ['view' => 'src/backend/api/web_publica/pagament-redsys-bizum.php', 'needs_session' => false, 'no_header_footer' => true],
];
