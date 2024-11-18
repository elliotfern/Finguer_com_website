<?php
global $conn;

$anyActual = date("Y");
$diaActual = date("d");
$mesActual = date("m");

echo "<div class='container'>";
    echo "<h2>Calendari d'entrades any en curs: ".$anyActual ."</h2>";
    echo "<ul>
            <li><h6><a href=' ".APP_WEB."/calendari/entrades/any/".$anyActual."/mes/".$mesActual."/dia/".$diaActual."'>Veure les entrades d'avui al parking</a></h6></li>
        </ul><br>";

    $sql = "SELECT CAST(rc1.diaEntrada AS DATE) AS mes
    FROM reserves_parking AS rc1
    WHERE YEAR(rc1.diaEntrada) = YEAR(CURRENT_TIMESTAMP())
    GROUP BY MONTH(rc1.diaEntrada)
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
                <th>Mes/any &darr;</th>
                </tr>
                </thead>
                <tbody>

	    <?php
        foreach($result as $row) {
            $mes = $row['mes'];
            $mes2 = date("m", strtotime($mes));
	        $any2 = date("Y", strtotime($mes));

            switch ($mes2) {
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
                echo "<tr>";
                echo "<td><a href='".APP_WEB."/calendari/entrades/any/".$any2."/mes/".$mes2."'>".$mes3." // ".$any2."</a></td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
    }

echo "</div>";
require_once(APP_ROOT . '/public/inc/footer.php');
?>