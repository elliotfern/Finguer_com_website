<h2>Estat 1: Reserves pendents d'entrada al párking</h2>
<h4>Ordenat segons data entrada vehicle</h4>

<div class="container-fluid">
<div class='table-responsive'>
<table class='table table-striped' id="pendents">
<thead class="table-dark">
    <tr>
                <th>Núm. Comanda // data</th>
                <th>Import</th>
                <th>Pagat</th>
                <th>Tipus</th>
                <th>Neteja</th>
                <th>Client // tel.</th>
                <th>Entrada &darr;</th>
                <th>Sortida</th>
                <th>Vehicle</th>
                <th>Vol tornada</th>
                <th>Check-in</th>
                <th>Notes</th>
                <th>Cercadors</th>
                <th>Email confirmació</th>
                <th>Factura</th>
                <th>Modificar</th>
                <th>Eliminar</th>
                </tr>
                </thead>
                <tbody>

           </tbody>
           </table>
         
    
<h5 id="numReservesPendents"></h5>
</div>
</div>
<script>
function fetch_data(){
    let urlAjax = window.location.origin + "/api/intranet/reserves/get/?type=pendents";
    $.ajax({
        url:urlAjax,
        method:"GET",
        dataType:"json",
        success:function(data){
            // formato fechas
            let opcionesFormato = { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            let opcionesFormato2 = { day: '2-digit', month: '2-digit', year: 'numeric' };
            let opcionesFormato3 = { year: 'numeric' };

            let html = '';
            for (let i=0; i<data.length; i++) {
                // Operaciones de manipulacion de las variables
                // a) Fecha reserva
                let fechaReservaString = data[i].fechaReserva;
                let fechaReservaDate = new Date(fechaReservaString);
                let fechaReserva_formateada = fechaReservaDate.toLocaleDateString('es-ES', opcionesFormato);

                // b) Fecha entrada
                let dataEntradaString = data[i].dataEntrada;
                let dataEntradaDate = new Date(dataEntradaString);
                let dataEntrada2 = dataEntradaDate.toLocaleDateString('es-ES', opcionesFormato2);
                let dataEntradaAny = dataEntradaDate.toLocaleDateString('es-ES', opcionesFormato3);

                // c) Fecha salida
                let dataSortidaString = data[i].dataSortida;
                let dataSortidaDate = new Date(dataSortidaString);
                let dataSortida2 = dataSortidaDate.toLocaleDateString('es-ES', opcionesFormato2);
                let dataSortidaAny = dataSortidaDate.toLocaleDateString('es-ES', opcionesFormato3);

                let tipo = data[i].tipo;
                let tipoReserva2 = "";
                if (tipo === 1) {
                    tipoReserva2 = "Finguer Class";
                } else if (tipo === 2) {
                    tipoReserva2 = "Gold Finguer Class";
                }

                let limpieza = data[i].limpieza;
                let limpieza2 = "";
                if (limpieza === 1) {
                    limpieza2 = "Servicio de limpieza exterior";
                } else if (limpieza === 2) {
                    limpieza2 = "Servicio de lavado exterior + aspirado tapicería interior";
                } else if (limpieza === 3) {
                    limpieza2 = "Limpieza PRO";
                } else {
                    limpieza2 = "-";
                }

                const urlWeb = window.location.origin + "/control"

                // 0 - Inicio construccion body tabla
                html += '<tr>';

                // 1 - IdReserva
                html += '<td>';
                if (data[i].idReserva == 1) {
                    html += '<button type="button" class="btn btn-primary btn-sm">Client anual</button>';
                } else {
                    html += data[i].idReserva + ' // ' + fechaReserva_formateada;
                }
                html += '</td>';

                // 2 - Import
                html += '<td><strong>' + data[i].importe + ' €</strong></td>';

                // 3 - Pagat
                html += '<td>';
                if (data[i].idReserva == 1) {
                    html += '<button type="button" class="btn btn-success">SI</button>';
                    html += '<p>Client anual</p>';
                } else {
                    if (data[i].processed === 1) {
                    html += '<button type="button" class="btn btn-success">SI</button>';
                    html += '<p><a href="' + urlWeb + '/reserva/verificar-pagament/' + data[i].id + '"><strong>Verificar pagament</a></p>';
                    } else {
                        html += '<button type="button" class="btn btn-danger">NO</button>';
                        html += '<p><a href="' + urlWeb + '/reserva/verificar-pagament/' + data[i].id + '"><strong>Verificar pagament</a></p>';
                    }
                }     
                html += '</td>';

                // 4 - Tipus de reserva
                html += '<td><a href="' + urlWeb + '/reserva/modificar/tipus/' + data[i].id + '"><strong>' + tipoReserva2 + '</a></strong></td>';

                // 5 - Neteja
                html += '<td>' + limpieza2 + '</td>';

                // 6 - Client i telefon
                html += '<td>';
                if (data[i].nombre) {
                    html += '<a href="' + urlWeb + '/reserva/modificar/nom/' + data[i].id + '">' + data[i].nombre + '</a> // <a href="' + urlWeb + '/reserva/modificar/telefon/' + data[i].id + '">' + data[i].tel + '</a>';
                } else {
                    html += data[i].clientNom + ' ' + data[i].clientCognom + ' // ' + data[i].telefono + '</a>';
                }
                html += '</td>';

                // 7 - Entrada (dia i hora)
                html += '<td>';
                if (dataEntradaAny == 1970) {
                    html += 'Pendent';
                } else {
                    html += '<strong><a href="' + urlWeb + '/reserva/modificar/entrada/' + data[i].id + '">' + dataEntrada2 + '//' + data[i].HoraEntrada + '</a></strong>';
                }
                html += '</td>';

                // 8 - Sortida (dia i hora)
                html += '<td>';
                if (dataSortidaAny == 1970) {
                    html += 'Pendent';
                } else {
                    html += '<a href="' + urlWeb + '/reserva/modificar/sortida/' + data[i].id + '">' + dataSortida2 + '</a> // <a href="' + urlWeb + '/reserva/modificar/sortida/' + data[i].id + '">' + data[i].HoraSortida + '</a>';
                }
                html += '</td>';

                // 9 - Vehicle i matricula
                html += '<td><a href="' + urlWeb + '/reserva/modificar/vehicle/' + data[i].id + '">' + data[i].modelo + '</a>';
                if (data[i].matricula) {
                    html += ' // <a href="' + urlWeb + '/reserva/modificar/vehicle/' + data[i].id + '">' + data[i].matricula + '</a>';
                } else {
                    html += '<p><a href="' + urlWeb + '/reserva/modificar/vehicle/' + data[i].id + '" class="btn btn-secondary btn-sm" role="button" aria-pressed="true">Afegir matrícula</a></p>';
                }
                html += '</td>';

                // 10 - Dades vol
                html += '<td>';
                if (!data[i].vuelo) {
                    html += '<a href="' + urlWeb + '/reserva/modificar/vol/' + data[i].id + '" class="btn btn-secondary btn-sm" role="button" aria-pressed="true">Afegir vol</a>';
                } else {
                    html += '<a href="' + urlWeb + '/reserva/modificar/vol/' + data[i].id + '">' + data[i].vuelo + '</a>';
                }
                html += '</td>';

                // 11 - CheckIn
                html += '<td>';
                if (data[i].checkIn === 5) {
                    html += '<a href="' + urlWeb + '/reserva/fer/check-in/' + data[i].id + '" class="btn btn-secondary btn-sm" role="button" aria-pressed="true">Check-In</a>';
                }
                html += '</td>';

                // 12 - Notes
                html += '<td>';
                if (!data[i].idReserva) {
                    html += '<a href="' + urlWeb + '/reserva/modificar/nota/' + data[i].id + '" class="btn btn-info btn-sm" role="button" aria-pressed="true">Crear</a>';
                } else if (data[i].idReserva && !data[i].notes) {
                    html += '<a href="' + urlWeb + '/reserva/modificar/nota/' + data[i].id + '" class="btn btn-info btn-sm" role="button" aria-pressed="true">Crear</a>';
                } else if (data[i].notes) {
                    html += '<a href="' + urlWeb + '/reserva/info/nota/' + data[i].id + '" class="btn btn-danger btn-sm" role="button" aria-pressed="true">Veure</a>'; 
                }
                html += '</td>';

                // 13 - Cercadors
                html += '<td>';
                if (data[i].idReserva == 1) {
                    html += '-';
                } else {
                    if (!data[i].idReserva) {
                        html += '<a href=' + urlWeb + '/reserva/modificar/cercador/' + data[i].id + '" class="btn btn-warning btn-sm" role="button" aria-pressed="true">Alta</a>';
                    } else if (data[i].idReserva && !data[i].buscadores) {
                        html += '<a href="' + urlWeb + '/reserva/modificar/cercador/' + data[i].id + '" class="btn btn-warning btn-sm" role="button" aria-pressed="true">Alta</a>';
                    } else if (data[i].buscadores) {
                        html += data[i].buscadores + ' <a href="' + urlWeb + '/reserva/modificar/cercador/' + data[i].id + '">(modificar)</a>';
                    }
                }
                html += '</td>';

                // 14 - Email confirmacio
                html += '<td><a href="' + urlWeb + '/reserva/email/confirmacio/' + data[i].id + '" class="btn btn-success btn-sm" role="button" aria-pressed="true"><i class="bi bi-envelope"></i></i></a></td>';
                
                // 15 - Enviar factura pdf
                html += '<td><a href="' + urlWeb + '/reserva/email/factura/' + data[i].id + '" class="btn btn-primary btn-sm" role="button" aria-pressed="true"><i class="bi bi-file-earmark-pdf"></a></td>';

                // 16 - Modificar reserva
                html += '<td><a href="' + urlWeb + '/reserva/modificar/reserva/' + data[i].id + '" class="btn btn-warning btn-sm" role="button" aria-pressed="true"><i class="bi bi-pencil-square"></i></a></td>';

                // 17 - Eliminar reserva
                html += '<td><a href="' + urlWeb + '/reserva/eliminar/reserva/' + data[i].id + '" class="btn btn-danger btn-sm" role="button" aria-pressed="true"><i class="bi bi-trash"></i></a></td>';

                html += '</tr>';
            }
            $('#pendents tbody').html(html);
        }
    });
}

function fetch_data2(){
    let urlAjax = window.location.origin + "/api/intranet/reserves/get/?type=numReservesPendents";
    $.ajax({
        url:urlAjax,
        method:"GET",
        dataType:"json",
        success:function(data){
            let html = '';
            for (let i=0; i<data.length; i++) {
                document.getElementById("numReservesPendents").textContent = "Total reserves pendents d'entrar al parking: " + data[i].numero;
            }
        }
    });
}

fetch_data();
fetch_data2();
</script>