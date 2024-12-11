<?php require_once APP_ROOT . '/public/intranet/inc/header.php'; ?>

<h2>Estat 3: Reserves completades amb check-out del parking</h2>
<h4>Ordenat segons data sortida vehicle</h4>

<div class="container-fluid">
    <div class='table-responsive' id='completades'>
        <table class='table table-striped'>
            <thead class="table-dark">
                <tr>
                    <th>Núm. Comanda // ID</th>
                    <th>Data reserva</th>
                    <th>Import</th>
                    <th>Tipus</th>
                    <th>Client // tel.</th>
                    <th>Factura</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <h5 id="numReservesCompletades"></h5>

</div>

<script>
    function fetch_data() {
        let urlAjax = window.location.origin + "/api/intranet/reserves/get/?type=completades";
        $.ajax({
            url: urlAjax,
            method: "GET",
            dataType: "json",
            success: function(data) {
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

                let html = '';
                for (let i = 0; i < data.length; i++) {
                    // Operaciones de manipulacion de las variables
                    // a) Fecha reserva

                    let fechaReserva_formateada; // Declaración fuera del bloque if

                    if (data[i].fechaReserva !== null) {
                        let fechaReservaString = data[i].fechaReserva;
                        let fechaReservaDate = new Date(fechaReservaString);
                        fechaReserva_formateada = fechaReservaDate.toLocaleDateString('es-ES', opcionesFormato);
                    } else {
                        fechaReserva_formateada = "-";
                    }

                    let tipo = data[i].tipo;
                    let tipoReserva2 = "";
                    if (tipo === 1) {
                        tipoReserva2 = "Finguer Class";
                    } else if (tipo === 2) {
                        tipoReserva2 = "Gold Finguer Class";
                    }

                    // 0 - Inicio construccion body tabla
                    html += '<tr>';

                    // 1 - IdReserva
                    html += '<td>';
                    if (data[i].idReserva == 1) {
                        html += '<button type="button" class="btn btn-primary btn-sm">Client anual</button>';
                    } else {
                        html += data[i].idReserva + ' // ' + data[i].id;
                    }
                    html += '</td>';

                    // 2 -  Data reserva
                    html += '<td>';
                    if (data[i].idReserva == 1) {
                        html += '<button type="button" class="btn btn-primary btn-sm">Client anual</button>';
                    } else {
                        html += fechaReserva_formateada;
                    }
                    html += '</td>';

                    // 3 -  Import
                    html += '<td>';
                    if (data[i].importe !== "") {
                        html += '<strong>' + data[i].importe + ' €</strong>';
                    } else {
                        html += '-';
                    }

                    html += '</td>';

                    // 4 - Tipus de reserva
                    html += '<td><strong>' + tipoReserva2 + '</strong></td>';

                    // 6 - Client i telefon
                    html += '<td>';
                    if (data[i].nombre) {
                        html += data[i].nombre + ' // ' + data[i].tel;
                    } else {
                        html += data[i].clientNom + ' ' + data[i].clientCognom + '// ' + data[i].telefono;
                    }
                    html += '</td>';

                    // 15 - Enviar factura pdf
                    html += '<td><a href="' + window.location.origin + '/reserva/email/factura/' + data[i].id + '" class="btn btn-primary btn-sm" role="button" aria-pressed="true">PDF</a></td>';

                    html += '</tr>';
                }
                $('#completades tbody').html(html);
            }
        });
    }

    function fetch_data2() {
        let urlAjax = window.location.origin + "/api/intranet/reserves/get/?type=numReservesCompletades";
        $.ajax({
            url: urlAjax,
            method: "GET",
            dataType: "json",
            success: function(data) {
                let html = '';
                for (let i = 0; i < data.length; i++) {
                    document.getElementById("numReservesCompletades").textContent = "Total reserves completades: " + data[i].numero;
                }
            }
        });
    }

    fetch_data();
    fetch_data2();
</script>