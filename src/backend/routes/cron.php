<?php

return [
    // TREBALLS CRON RESERVES - SENSE SESSIO PRIVADA
    '/cron/reserves' => ['view' => 'public/cron/cron-reserves.php', 'needs_session' => false],

    '/cron/pagats' => ['view' => 'public/cron/cron-pagats.php', 'needs_session' => false],
];
