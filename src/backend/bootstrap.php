<?php

require_once __DIR__ . '../../../vendor/autoload.php';

use Dotenv\Dotenv;

$basePath = __DIR__ . '/../..';

if (file_exists($basePath . '/.env')) {
    Dotenv::createImmutable($basePath)->load();
}


// Incluir configuraciones y rutas
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/utils/uuidv7.php';
require_once __DIR__ . '/utils/getIdUserCookie.php';
require_once __DIR__ . '/utils/1_verificaPagamentRedsys.php';
require_once __DIR__ . '/utils/1_1_lecturaReserva.php';
require_once __DIR__ . '/utils/1_2_consultaPagamentRedsys.php';
require_once __DIR__ . '/utils/1_3_registrarCobroConfirmado.php';
require_once __DIR__ . '/utils/1_4_enviarConfirmacionReserva.php';
require_once __DIR__ . '/utils/1_5_creacionFacturaParaReserva.php';
require_once __DIR__ . '/utils/1_6_generarFacturaPdf.php';
require_once __DIR__ . '/utils/1_7_enviarFacturaEmail.php';

require_once __DIR__ . '/utils/reserva/cambiarEstadoReserva.php';
require_once __DIR__ . '/utils/reserva/cancelarReserva.php';
require_once __DIR__ . '/utils/reserva/reservaEstadoExceptions.php';
require_once __DIR__ . '/utils/verificacioSessio.php';

require_once __DIR__ . '/utils/generarNumeroFactura.php';
require_once __DIR__ . '/utils/registreLogsFactura.php';
require_once __DIR__ . '/utils/calcularHashFactura.php';
require_once __DIR__ . '/utils/generadorLocalizador.php';
require_once __DIR__ . '/utils/helpers.php';
require_once __DIR__ . '/routes/routes.php';
require_once __DIR__ . '/utils/auth.php';
require_once __DIR__ . '/utils/logoutDeleteCookies.php';
require_once __DIR__ . '/utils/email-formulario.php';