<?php

return [

    // 05. Area cliente - necesita verificacion
    '/area-cliente/login' => ['view' => 'public/area-client/login.php', 'needs_session' => false, 'needs_verification' => false, 'no_header_footer' => false],

    '/area-cliente' => ['view' => 'public/area-client/index.php', 'needs_session' => false, 'needs_verification' => true, 'no_header_footer' => false],

    '/area-cliente/validar-token' => ['view' => 'public/area-client/validar-token.php', 'needs_session' => false, 'needs_verification' => false, 'no_header_footer' => true],

    '/area-cliente/reservas' => ['view' => 'public/area-client/index.php', 'needs_session' => false, 'needs_verification' => true, 'no_header_footer' => false],
];
