<?php require_once APP_ROOT . '/public/intranet/inc/header.php';?>

<div class="container">
    <h2>Estat 1: Reserves pendents d'entrada al párking</h2>
    <h4>Ordenat segons data entrada vehicle</h4>
</div>

<div class="container">
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
                    <th>Dades Vehicle</th>
                    <th>Vol tornada</th>
                    <th>Check-in</th>
                    <th>Notes</th>
                    <th>Cercadors</th>
                    <th>Opcions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<!-- Ventana emergente -->
<div id="ventanaEmergente" class="ventana" style="display: none; position: absolute; background: white; border: 1px solid #ccc; padding: 20px; border-radius: 8px;">
        <div class="contenidoVentana">
                <div class="container">
                    <div class="row">
                        <div class="col-12 col-md-12 d-flex flex-column justify-content-between gap-3">
                            <button id="enlace1" class="btn btn-secondary  w-100 w-md-auto btn-sm" role="button" aria-disabled="false">Enviar confirmació</button>
                
                            <button id="enlace2" class="btn btn-secondary  w-100 w-md-auto btn-sm" role="button" aria-disabled="false">Enviar factura</button>

                            <a href="#" id="enlace3" class="btn btn-secondary  w-100 w-md-auto btn-sm" role="button" aria-disabled="false">Modificar reserva</a>

                            <button id="enlace4" class="btn btn-secondary  w-100 w-md-auto btn-sm" role="button" aria-disabled="false">Eliminar reserva</button>

                            <a href="#" id="cerrarVentana" class="btn btn-danger  w-100 w-md-auto btn-sm" role="button" aria-disabled="false">Tancar</a>
                        </div>
                    </div>
                </div>
        </div>
    </div>

<div class="container" style="margin-bottom:50px">
    <h5 id="numReservesPendents"></h5>
</div>

<script>



function btnEnviarConfirmacio(id) {
    let urlAjax = window.location.origin + "/api/intranet/email/get/?type=emailConfirmacioReserva&id=" + id;
    $.ajax({
        url:urlAjax,
        method:"GET",
        dataType:"json",
        success: function(data) {
            // Verificamos si la respuesta es "success"
            if (data.message === "success") {
                const boton = document.getElementById('enlace1');
                boton.textContent = "Email enviat!"; 

                // Cambiar el estilo del botón (puedes agregar una clase CSS como ejemplo)
                boton.classList.add("btn-success"); // Cambiar el color del botón
                boton.classList.remove("btn-secondary"); // Eliminar el estilo original

                // Desactivar el botón
                boton.disabled = true;

                // Desactivar el cursor para reflejar el estado desactivado visualmente
                boton.style.cursor = "not-allowed";
                boton.style.opacity = "0.5";

            } else {
                // Aquí podrías manejar otros casos si la respuesta no es "success"
                console.log("Error al enviar el email");
            }
        },
        error: function(xhr, status, error) {
            // Manejar el caso de error en la solicitud Ajax
            console.error("Error en la solicitud AJAX:", error);
        }
    });
}

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

                const urlWeb = window.location.origin + "/control";

                function formatImporte(importe) {
                    const numero = parseFloat(importe);
                    if (isNaN(numero)) {
                        const numero = 0.0;
                        const [entero, decimal] = numero.toFixed(2).split('.'); return `${entero},${decimal}`; 
                    } else {
                       const [entero, decimal] = numero.toFixed(2).split('.'); return `${entero},${decimal}`; 
                    }
                }

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
                html += '<td><strong>' + formatImporte(data[i].importe) + ' €</strong></td>';

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
                html += '<td><strong>' + tipoReserva2 + '</strong></td>';

                // 5 - Neteja
                html += '<td>' + limpieza2 + '</td>';

                // 6 - Client i telefon
                html += '<td>';
                if (data[i].nombre) {
                    html += data[i].nombre + ' // ' + data[i].tel;
                } else {
                    html += data[i].clientNom + ' ' + data[i].clientCognom + ' // ' + data[i].telefono;
                }
                html += '</td>';

                // 7 - Entrada (dia i hora)
                html += '<td>';
                if (dataEntradaAny == 1970) {
                    html += 'Pendent';
                } else {
                    html += '<strong>' + dataEntrada2 + ' // ' + data[i].HoraEntrada + '</strong>';
                }
                html += '</td>';

                // 8 - Sortida (dia i hora)
                html += '<td>';
                if (dataSortidaAny == 1970) {
                    html += 'Pendent';
                } else {
                    html += dataSortida2 + ' // ' + data[i].HoraSortida;
                }
                html += '</td>';

                // 9 - Vehicle i matricula
                html += '<td>' + data[i].modelo;
                if (data[i].matricula) {
                    html += ' // ' + data[i].matricula;
                } else {
                    html += '<p><a href="' + urlWeb + '/reserva/modificar/vehicle/' + data[i].id + '" class="btn btn-secondary btn-sm" role="button" aria-pressed="true">Afegir matrícula</a></p>';
                }

                if (data[i].numeroPersonas) {
                    html += '<p> // ' + data[i].numeroPersonas + ' personas</p>';
                } else {
                    html += ' // -';
                }
                html += '</td>';

                // 10 - Dades vol
                html += '<td>';
                if (!data[i].vuelo) {
                    html += '<a href="' + urlWeb + '/reserva/modificar/vol/' + data[i].id + '" class="btn btn-secondary btn-sm" role="button" aria-pressed="true">Afegir vol</a>';
                } else {
                    html +=  data[i].vuelo;
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
                html += '<td><button id="obrirFinestraBtn" class="btn btn-success btn-sm" role="button" aria-pressed="true" data-id="' + data[i].id + '">Obrir</button></td>';
                
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