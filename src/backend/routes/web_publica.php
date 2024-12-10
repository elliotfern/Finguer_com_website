<?php

return [
    // 01. Home
    '/' => ['view' => 'public/web-publica/index.php', 'needs_session' => false],

    '/fr' => ['view' => 'public/web-publica/index.php', 'needs_session' => false],

    '/en' => ['view' => 'public/web-publica/index.php', 'needs_session' => false],

    // 02. Motor reserves
    '/pago' => ['view' => 'public/web-publica/pagina_pago.php', 'needs_session' => false],

    '/fr/pago' => ['view' => 'public/web-publica/pagina_pago.php', 'needs_session' => false],

    '/en/pago' => ['view' => 'public/pagina_pago.php', 'needs_session' => false],

    // 03. Post redirecciones redsys
    '/compra-realizada' => ['view' => 'public/web-publica/pagina_exito.php', 'needs_session' => false],

    '/error-compra' => ['view' => 'public/web-publica/pagina_error.php', 'needs_session' => false],

    // 04. Legal
    '/politica-de-privacidad-finguer' => ['view' => 'public/web-publica/politica-privacidad.php', 'needs_session' => false],

    '/terminos-y-condiciones' => ['view' => 'public/web-publica/terminos-condiciones.php', 'needs_session' => false],
];
