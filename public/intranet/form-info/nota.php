<?php
global $conn;

$id = $routeParams[0];

if (is_numeric($id)) {
    $id_old = intval($id);
    
    if ( filter_var($id_old, FILTER_VALIDATE_INT) ) {
        $codi_resposta = 2;

        echo "<div class='container'>";

        // consulta general reserves 
        $sql = "SELECT r.idReserva, r.notes
        FROM reserves_parking AS r
        WHERE r.id = $id_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach ($result as $row) {
            $idReserva_old = $row['idReserva'];
            $notes_old = $row['notes'];
        }

        if ($idReserva_old == 1) {
            echo "<h2>Veure notes a la reserva client anual amb ID núm: ".$id_old." </h2>";
        } else {
            echo "<h2>Veure notes a la reserva núm: ".$idReserva_old." </h2>";
        }          

            echo "<p>".$notes_old."</p>";
            echo "<p><a href='".APP_WEB."/reserva/modificar/nota/".$id_old."'>Vols modificar aquesta nota?</a></p>";
           
        
    } else {
        echo "Error: aquest ID no és vàlid";
    }
} else {
    echo "Error. No has seleccionat cap vehicle.";
}

echo "</div>";
?>