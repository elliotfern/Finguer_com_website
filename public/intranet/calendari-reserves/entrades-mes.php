<?php
global $conn;

$year_old = $params['any'];
$month_old = $params['mes'];

    switch ($month_old) {
        case '01': $mes3 = "Gener";
                       break;
        case '02': $mes3 = "Febrer";
                       break;
        case '03':  $mes3 = "Març";
                       break;
        case '04':  $mes3 = "Abril";
                        break;
        case '05':  $mes3 = "Maig";
                        break;
        case '06':  $mes3 = "Juny";
                        break;
        case '07':  $mes3 = "Juliol";
                        break;
        case '08':  $mes3 = "Agost";
                        break;
        case '09':  $mes3 = "Setembre";
                        break;
        case '10':  $mes3 = "Octubre";
                        break;
        case '11':  $mes3 = "Novembre";
                        break;
        case '12':  $mes3 = "Desembre";
                        break;
    }

$anyActual = date("Y");

    echo "<div class='container'>
    <h2>Calendari d'entrades: ".$mes3." // ".$year_old ."</h2>";

    $sql = "SELECT CAST(rc1.diaEntrada AS DATE) AS mes
    FROM reserves_parking AS rc1
    left join reservas_buscadores AS b ON rc1.buscadores = b.id
    WHERE YEAR(rc1.diaEntrada) = $year_old AND MONTH(rc1.diaEntrada) = $month_old
    GROUP BY DAY(rc1.diaEntrada)
    ORDER BY rc1.diaEntrada ASC, rc1.horaEntrada ASC";

    $pdo_statement = $conn->prepare($sql);
    $pdo_statement->execute();
    $result = $pdo_statement->fetchAll();
    if (!empty($result)) {
        ?>
        <div class="container-lg">
        <div class='table-responsive'>
        <table class='table table-striped'>
        <thead class="table-dark">
            <tr>
                <th>Veure entrades per dia &darr;</th>
                </tr>
                </thead>
                <tbody>

	    <?php
        foreach($result as $row) {
            $mes = $row['mes'];
	        $dia2 = date("d", strtotime($mes));
	        $mes2 = date("m", strtotime($mes));
	        $any2 = date("Y", strtotime($mes));

                echo "<tr>";
                echo "<td><a href='".APP_WEB."/calendari/entrades/any/".$any2."/mes/".$mes2."/dia/".$dia2."'>".$dia2."/".$mes2."/".$any2."</a></td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
    }


    echo "</div>";
    require_once(APP_ROOT . '/public/inc/footer.php');
    ?>
