<?php

require_once __DIR__ . '../../../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Infrastructure\Persistence\MySql\MysqlConnection;

$basePath = __DIR__ . '/../..';

if (file_exists($basePath . '/.env')) {
    Dotenv::createImmutable($basePath)->load();
}

$conn = MysqlConnection::get();

// Incluir configuraciones y rutas
require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/Utils/uuidv7.php';
require_once __DIR__ . '/Utils/getIdUserCookie.php';
require_once __DIR__ . '/Utils/1_verificaPagamentRedsys.php';
require_once __DIR__ . '/Utils/1_1_lecturaReserva.php';
require_once __DIR__ . '/Utils/1_3_registrarCobroConfirmado.php';
require_once __DIR__ . '/Utils/1_4_enviarConfirmacionReserva.php';
require_once __DIR__ . '/Utils/1_5_creacionFacturaParaReserva.php';
require_once __DIR__ . '/Utils/1_6_generarFacturaPdf.php';
require_once __DIR__ . '/Utils/1_7_enviarFacturaEmail.php';

require_once __DIR__ . '/Utils/Reserva/cambiarEstadoReserva.php';
require_once __DIR__ . '/Utils/Reserva/cancelarReserva.php';
require_once __DIR__ . '/Utils/Reserva/reservaEstadoExceptions.php';
require_once __DIR__ . '/Utils/verificacioSessio.php';

require_once __DIR__ . '/Utils/generarNumeroFactura.php';
require_once __DIR__ . '/Utils/registreLogsFactura.php';
require_once __DIR__ . '/Utils/calcularHashFactura.php';
require_once __DIR__ . '/Utils/generadorLocalizador.php';
require_once __DIR__ . '/Utils/helpers.php';
require_once __DIR__ . '/routes/routes.php';
require_once __DIR__ . '/Utils/auth.php';
require_once __DIR__ . '/Utils/logoutDeleteCookies.php';
require_once __DIR__ . '/Utils/email-formulario.php';
