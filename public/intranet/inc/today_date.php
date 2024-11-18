<?php
// ID USER from cookie
$user_id = isset($_COOKIE['user_id']) ? intval($_COOKIE['user_id']) : null;

// Allowed users
$allowedUserIds = array(1,4); // Caroline, Tina

// Establecer la zona horaria predeterminada
date_default_timezone_set('Europe/Dublin');

// Obtener la fecha actual
$dayOfWeek = date('l'); // Día de la semana en texto completo
$day = date('j'); // Día del mes sin ceros iniciales
$month = date('F'); // Mes en texto completo
$year = date('Y'); // Año en 4 dígitos

$month_number = date('m');
$day_number = date('d');

// Fecha de hoy en formato MySQL (YYYY-MM-DD)
$datetToday = "$year-$month-$day";

// Función para obtener el sufijo del día
function getDaySuffix($day) {
    if (!in_array(($day % 100), [11, 12, 13])) {
        switch ($day % 10) {
            case 1:
                return 'st';
            case 2:
                return 'nd';
            case 3:
                return 'rd';
        }
    }
    return 'th';
}

// Construir la fecha con el sufijo correcto
$daySuffix = getDaySuffix($day);
$formattedDate = "$dayOfWeek $day$daySuffix $month $year";
?>