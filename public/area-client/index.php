<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_COOKIE['user_id'])) {
    // Priorizar el valor de POST si está presente
    if (isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];
    }
    // Si no viene por POST, intentar recuperarlo de la cookie
    elseif (isset($_COOKIE['user_id'])) {
        $userId = $_COOKIE['user_id'];
        $email = $_COOKIE['email'];
    }
    // Si no está en POST ni en la cookie, denegar acceso
    else {
        echo '<script type="text/javascript">window.location.href = "/area-cliente/login";</script>';
        exit();  // Termina la ejecución del script después de la redirección
    }
} else {
    echo '<script type="text/javascript">window.location.href = "/area-cliente/login";</script>';
    exit();  // Termina la ejecución del script después de la redirección
}

?>

<div class="container" style="margin-bottom:300px;margin-top:50px">
    <h4>Histórico de reservas en Finguer.com</h4>
    <div id="table-container" class="table-responsive">
        <!-- Aquí se generará la tabla dinámicamente desde JS -->
    </div>
</div>
<script>
    function fetch_data(email) {
        let urlAjax = window.location.origin + "/api/area-client/reservas/?type=reservas&cliente=" + email;
        $.ajax({
            url: urlAjax,
            method: "GET",
            dataType: "json",
            success: function(data) {
                // Si la API devuelve el error "No rows found", mostrar mensaje
                if (data.error === "No rows found") {
                    $('#table-container').html('<p>No hay ninguna reserva</p>');
                    return; // Salir de la función si no hay datos
                }

                // Si no hay datos, mostrar mensaje de "No hay reservas"
                if (data.length === 0) {
                    $('#table-container').html('<p>No hay ninguna reserva</p>');
                    return; // Salir de la función si no hay datos
                }

                // formato fechas
                let opcionesFormato = {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                };
                let opcionesFormato2 = {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                };
                let opcionesFormato3 = {
                    year: 'numeric'
                };

                let table = '<table class="table table-striped" id="pendents">';
                table += '<thead class="table-dark"><tr>' +
                    '<th>Núm. Reserva / Fecha</th>' +
                    '<th>Importe</th>' +
                    '<th>Pagado</th>' +
                    '<th>Tipo</th>' +
                    '<th>Limpieza</th>' +
                    '<th>Entrada &darr;</th>' +
                    '<th>Salida</th>' +
                    '<th>Vehículo</th>' +
                    '<th>Factura</th>' +
                    '</tr></thead>';
                table += '<tbody>';

                for (let i = 0; i < data.length; i++) {
                    // Formato de fechas
                    let fechaReservaString = data[i].fechaReserva;
                    let fechaReservaDate = new Date(fechaReservaString);
                    let fechaReserva_formateada = fechaReservaDate.toLocaleDateString('es-ES', opcionesFormato);

                    let dataEntradaString = data[i].dataEntrada;
                    let dataEntradaDate = new Date(dataEntradaString);
                    let dataEntrada2 = dataEntradaDate.toLocaleDateString('es-ES', opcionesFormato2);
                    let dataEntradaAny = dataEntradaDate.toLocaleDateString('es-ES', opcionesFormato3);

                    let dataSortidaString = data[i].dataSortida;
                    let dataSortidaDate = new Date(dataSortidaString);
                    let dataSortida2 = dataSortidaDate.toLocaleDateString('es-ES', opcionesFormato2);
                    let dataSortidaAny = dataSortidaDate.toLocaleDateString('es-ES', opcionesFormato3);

                    let tipo = data[i].tipo;
                    let tipoReserva2 = tipo === 1 ? "Finguer Class" : tipo === 2 ? "Gold Finguer Class" : "";

                    let limpieza = data[i].limpieza;
                    let limpieza2 = limpieza === 1 ? "Servicio de limpieza exterior" : limpieza === 2 ? "Servicio de lavado exterior + aspirado tapicería interior" : limpieza === 3 ? "Limpieza PRO" : "-";

                    let html = '<tr>';
                    html += '<td>' + (data[i].idReserva === 1 ? '<button type="button" class="btn btn-primary btn-sm">Client anual</button>' : data[i].idReserva + ' // ' + fechaReserva_formateada) + '</td>';
                    html += '<td><strong>' + data[i].importe + ' €</strong></td>';
                    html += '<td>' + (data[i].processed === 1 ? '<button type="button" class="btn btn-success">SI</button>' : '<button type="button" class="btn btn-danger">NO</button>') + '</td>';
                    html += '<td><strong>' + tipoReserva2 + '</strong></td>';
                    html += '<td>' + limpieza2 + '</td>';
                    html += '<td>' + (dataEntradaAny === 1970 ? 'Pendiente' : '<strong>' + dataEntrada2 + ' // ' + data[i].HoraEntrada + '</strong>') + '</td>';
                    html += '<td>' + (dataSortidaAny === 1970 ? 'Pendiente' : dataSortida2 + ' // ' + data[i].HoraSortida) + '</td>';
                    html += '<td>' + data[i].modelo + (data[i].matricula ? ' // ' + data[i].matricula : '') + '</td>';

                    if (data[i].processed === 1) {
                        html += '<td><button class="btn btn-primary btn-sm" role="button" aria-pressed="true" onClick="enviarFactura(' + data[i].id + ');"><i class="bi bi-file-earmark-pdf"></i></button></td>';
                    } else {
                        html += '<td></td>';
                    }
                    html += '</tr>';

                    table += html;
                }

                table += '</tbody></table>';
                $('#table-container').html(table); // Inserta la tabla en el contenedor
            }
        });
    }

    function enviarFactura(id) {
        let urlAjax = window.location.origin + "/api/area-client/reservas/?type=factura&cliente=" + id;
        $.ajax({
            url: urlAjax,
            method: "GET",
            dataType: "json",
            data: {
                id: id
            },
            success: function(data) {
                // Aquí puedes agregar código para manejar la respuesta, si es necesario.
            }
        });
    }

    fetch_data("<?php echo $email; ?>");
</script>