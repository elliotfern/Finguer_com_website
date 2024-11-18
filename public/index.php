
    <div class="container" style="margin-bottom:100px">
    <div class="container text-center">
		
        <h1 class="wp-block-heading has-text-align-center"><strong>Finguer,<br>la forma más rápida y segura de aparcar<br>tu coche y llegar al aeropuerto de Barcelona</strong></h1>

        <div class="container justify-content-center" style="max-width:600px">
        <img class="img-responsive" src="<?php APP_ROOT;?>/public/img/avion-finguer.jpg" alt="Finguer, parking aeropuerto de Barcelona">
        </div>

        <p>Finguer, park your problems and fly away</p>
    </div>

<div class="container" style="margin-top:25px">
    <div class="row">
        <div class="col-12 col-md-6 mb-4 text-end">
        <img class="img-responsive" src="<?php APP_ROOT;?>/public/img/tarifas-esp.jpg" alt="Tarifas del parking">
        </div>
    
        <div class="col-12 col-md-6 mb-4 text-start quadre_reserves">
        <h1>Configura tu reserva:</h1>

        <!-- Selección del tipo de reserva -->
        <label for="tipo_reserva">Tipo de Reserva:</label>
        <select id="tipo_reserva" name="tipo_reserva">
            <option value="finguer_class">FINGUER CLASS</option>
            <option value="gold_finguer">GOLD FINGUER</option>
        </select>

        <!-- Calendario para elegir fecha de entrada y salida -->
        <form id="reservation-form">
            <label for="fecha_reserva">Fechas de Reserva:</label>
            <input type="text" id="fecha_reserva" name="fecha_reserva" readonly placeholder="Clique aquí para abrir el calendario">

            <!-- Opciones de limpieza -->
            <label for="limpieza">¿Quieres añadir un servicio de limpieza a tu reserva?</label>
            <select id="limpieza" name="limpieza">
                <option value="0">Sin limpieza</option>
                <option value="15">Servicio de limpieza exterior (15€ IVA incluido)</option>
                <option value="25">Servicio de lavado exterior + aspirado tapicería interior (25€ IVA incluido)</option>
                <option value="55">Lavado PRO. Lo dejamos como nuevo (55€ IVA incluido)</option>
            </select>

             <!-- Opciones de limpieza -->
             <label for="cancelacion">¿Deseas añadir un seguro de cancelación en caso de querer anular la reserva? (opcional)</label>

             <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" id="seguroSi" name="seguroCancelacion" value="1">
                <label class="form-check-label" for="seguroSi">Sí</label>
            </div>
            
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" id="seguroNo" name="seguroCancelacion" value="2" checked>
                <label class="form-check-label" for="seguroNo">No</label>
            </div>

            <!-- Espacio para mostrar el precio total y el número de días -->
            <p id="costeReserva" style="display:none;"></p>
            <p id="costeSeguro" style="display:none;"></p>
            <p id="costeLimpieza" style="display:none;"></p>
            <p id="subTotal" style="display:none;"></p>
            <p id="precio_iva" style="display:none;"></p>
            <p id="total" style="display:none;"></p>

            <!-- Espacio para mostrar mensajes de error -->
            <div id="mensaje_error" style="color: red;"></div>

            <!-- Botón de pagar -->
            <button type="button" id="pagar" style="display: none;">Pagar</button>
        </form>
   
        </div>
  </div>

</div>

<div class="container text-center" style="margin-top:30px">
<h2>¿Te imaginas un finguer<br>desde tu parking<br>hasta el aeropuerto El Prat?</h2>

<p>Llegas al aeropuerto de Barcelona - El Prat, embarcas la maleta, pasas todos los controles, llegas a tu puerta de embarque y rezas muy fuerte: “por favor, que me toque finguer…” Y justo la respuesta a esta plegaria es la diferencia entre que un viaje empiece muy bien o empiece regulín.</p>

<p >Pues ahora, esa sensación de que te ha tocado finguer, la puedes tener cuando piensas en qué hacer con tu coche cuando vas a viajar en avión. Porque cuando contratas Finguer, aparcas tus problemas y disfrutas más del viaje.</p>

<img class="img-responsive" src="<?php APP_ROOT;?>/public/img/finguer-park.jpg" alt="Finguer">
</div>

<div class="container text-center" id="servicios" style="margin-top:50px">
    <h2>Nosotros cuidamos de tu coche<br>y tú solo te preocupas de disfrutar del viaje</h2>
    <h4><strong>En Finguer tenemos 3 modalidades de servicio:</strong></h4>

    <div class="row text-center justify-content-center" style="margin-top:20px">
        <div class="col-12 col-md-3 mx-3 finguer1 mb-4" style="background-color:red">
            <h3 class="wp-block-heading"><strong>Finguer Class</strong></h3>
            <img class="img-responsive" src="/img/furgo.svg" alt="Finguer class">
            <hr>
            <p>Esta es la opción para los que quieren llegar en 3 minutos a su terminal. Llegas a Finguer, aparcas tu coche y en 3 minutos te llevamos en nuestro vehículo privado a tu terminal de salida.</p>
            <p>Y a la vuelta de tu viaje, igual de rápido.</p>
            <p>En cuanto salgas de la terminal te estaremos esperando para llevarte de nuevo con<br>tu querido coche.</p>
            <strong>Desde 15 €/día</strong>
        </div>

        <div class="col-12 col-md-3 mx-3 finguer2 mb-4" style="background-color:red">
            <h3><strong>Gold Finguer Class</strong></h3>
            <img class="img-responsive" src="<?php APP_ROOT;?>/public/img/keys.svg" alt="Gold finguer class">
            <hr>
            <p>Este servicio es el que escogería el mismísimo James Bond.</p>
            <p>Con la Gold Finguer Class solo tienes que llegar con tu coche a la terminal que te haya tocado (muy importante que nos avises 20 minutos antes de llegar), darnos las llaves del coche y disfrutar de tu viaje. Y a tu vuelta, pues lo mismo. Llegas, sales de la terminal y allí te estaremos esperando con tu coche.</p>
            <strong>Desde 25 €/día</strong>
        </div>

        <div class="col-12 col-md-3 mx-3 finguer3 mb-4" style="background-color:red">
            <h3><strong>Anual Finguer Class</strong></h3>
            <img class="img-responsive" src="<?php APP_ROOT;?>/public/img/icons-fingueranualclass-gris.svg" height="60" alt="Anual Finguer class">
            <hr>
            <p>Este es el servicio anual de Finguer.</p>
            <p>Con Annual Finguer Class podrás disfrutar de un servicio y una atención completamente personalizada. Aparca tu coche el tiempo y las veces que quieras.</p>
            <p>Para la contratación de este servicio totalmente adaptado a ti, deberás ponerte en contacto con nosotros a través de email para solicitar tu presupuesto sin compromiso.</p>
        </div>
    </div>
</div>

<div class="container text-center" style="background-color:#98b0dc;margin-top:55px;padding:25px;border-radius: 25px;">
<p><strong>* Escojas la modalidad que escojas es imprescindible reservar con una antelación de 12 horas. Así podemos tenerlo todo listo para cuando te vayas y cuando llegues.</strong></p>

<p ><strong>* Después de las 24h habrá un cargo de 10 euros por cada hora suplementaria.</strong></p>

<p><strong>* Finguer recomienda a todos sus clientes que a la hora de hacer su reserva prevean un margen de tiempo suficiente para no perder su vuelo. Finguer no se responsabilizará de las pérdidas de vuelos.</strong></p>
</div>

<div class="container" style="margin-top: 55px">
<h2 class="text-center"><strong>Servicios</strong></h2>

<div class="row text-center justify-content-center servicios" style="margin-top:20px">
        <div class="col-12 col-md-2 mx-3">
            <img class="img-responsive" src="<?php APP_ROOT;?>/public/img/icons-pago-online.svg" alt="Servicios">
            <p><strong>Pago online</strong><br><strong>seguro</strong></p>
        </div>

        <div class="col-12 col-md-2 mx-3">
            <img class="img-responsive" src="<?php APP_ROOT;?>/public/img/icons-servicio-personalizado.svg" alt="Servicio personalizado" height="140">
            <p><strong>Servicio</strong><br><strong>personalizado</strong></p>
        </div>

        <div class="col-12 col-md-2 mx-3">
            <img class="img-responsive" src="<?php APP_ROOT;?>/public/img/icons-mascarilla.svg" alt="Máxima seguridad e higiene" height="140">
            <p><strong>Máxima seguridad</strong><br><strong>e higiene</strong></p>
        </div>

        <div class="col-12 col-md-2 mx-3">
            <img class="img-responsive" src="<?php APP_ROOT;?>/public/img/icons-parking-lavado.png" alt="Servicios de lavado" height="140">
            <p><strong>Servicio </strong><br><strong>de lavado</strong></p>
        </div>

        <div class="col-12 col-md-2 mx-3">
            <img class="img-responsive" src="<?php APP_ROOT;?>/public/img/icons-parking-vigilado.svg" alt="Vigilado 24h" height="140">
            <p><strong>Parking</strong><br><strong>vigilado 24 h</strong></p>
        </div>
</div>

<div class="container text-center bloc1">
    <strong>Finguer es un parking para coches con servicio de traslado y recogida al aeropuerto de Barcelona. Pero nosotros nos consideramos más como un hotel para mascotas. Y es que en Finguer tratamos tu coche como si fuera tu perro o gato: lo vigilamos, lo lavamos, cuidamos que no se pelee con el resto de coches… Y si no tienes mascota, pero te lo estás pensando, los científicos que saben de esto aconsejan el <a href="https://hipertextual.com/2016/07/mascotas-exoticas" target="_blank" rel="noreferrer noopener">ciervo sika</a>, originario de Japón. ???</strong>
</div>

<div class="container" style="margin-top:55px">
    <div class="row">
        <div class="col-12 col-md-2" id="donde-estamos">
        <h2><strong>¿Dónde estamos?</strong></h2>

        <p><strong>Nuestro horario de servicio es de</strong><br><strong>5 de la madrugada a 23:30 de la noche</strong></p>

        <p id="contacto"><a href="https://www.google.com/maps/dir//41.3077704,2.0662899/@41.307698,2.066425,10z?hl=es" target="_blank" rel="noreferrer noopener">Carrer de l'Alt Camp, 9, 08830</a><br><a href="https://www.google.com/maps/dir//41.3077704,2.0662899/@41.307698,2.066425,10z?hl=es" target="_blank" rel="noreferrer noopener">Sant Boi de Llobregat, Barcelona</a></p>


        <p><strong>Teléfonos de contacto</strong><a href="https://wa.link/1qe0pe" target="_blank"></a></p>
        <p><a href="tel:+34689255821">+34 689 255 821</a><br>
        <a href="mailto:hello@finguer.com">hello@finguer.com</a></p>
        </div>

        <div class="col-12 col-md-10">
            <img class="img-responsive" src="<?php APP_ROOT;?>/public/img/mapa-finguer.jpg" alt="Mapa Finguer localizacion">
        </div>
    </div>
</div>

</div>
</div>