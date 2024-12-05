import { Reserva } from '../../../../types/interfaces';

export const carregarDadesTaulaReservesPendents = async (): Promise<void> => {
  try {
    // Corregir la URL
    const url = `${window.location.origin}/api/intranet/reserves/get/?type=pendents`;

    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const datos: Reserva[] = await response.json();

    // Formato de fechas
    const opcionesFormato: Intl.DateTimeFormatOptions = {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
    };

    const opcionesFormato2: Intl.DateTimeFormatOptions = {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    };

    const opcionesFormato3: Intl.DateTimeFormatOptions = {
      year: 'numeric',
    };

    // Procesar los datos para renderizar la tabla
    const tableBody = document.querySelector('#pendents tbody') as HTMLTableSectionElement;
    // Limpiar el cuerpo de la tabla antes de agregar nuevos datos
    tableBody.innerHTML = '';

    datos.forEach((data: Reserva) => {
      const fila = document.createElement('tr');
      let html = ''; // Declarar la variable html

      // Operaciones de manipulación de las variables
      // a) Fecha reserva
      const fechaReservaString = data.fechaReserva;
      const fechaReservaDate = new Date(fechaReservaString);
      const fechaReserva_formateada = fechaReservaDate.toLocaleDateString('es-ES', opcionesFormato);

      // b) Fecha entrada
      const dataEntradaString = data.dataEntrada;
      const dataEntradaDate = new Date(dataEntradaString);
      const dataEntrada2 = dataEntradaDate.toLocaleDateString('es-ES', opcionesFormato2);
      const dataEntradaAny = dataEntradaDate.toLocaleDateString('es-ES', opcionesFormato3);

      // c) Fecha salida
      const dataSortidaString = data.dataSortida;
      const dataSortidaDate = new Date(dataSortidaString);
      const dataSortida2 = dataSortidaDate.toLocaleDateString('es-ES', opcionesFormato2);
      const dataSortidaAny = dataSortidaDate.toLocaleDateString('es-ES', opcionesFormato3);

      const tipo = data.tipo;
      let tipoReserva2 = '';
      if (tipo === 1) {
        tipoReserva2 = 'Finguer Class';
      } else if (tipo === 2) {
        tipoReserva2 = 'Gold Finguer Class';
      }

      const limpieza = data.limpieza;
      let limpieza2 = '';
      if (limpieza === 1) {
        limpieza2 = 'Servicio de limpieza exterior';
      } else if (limpieza === 2) {
        limpieza2 = 'Servicio de lavado exterior + aspirado tapicería interior';
      } else if (limpieza === 3) {
        limpieza2 = 'Limpieza PRO';
      } else {
        limpieza2 = '-';
      }

      const urlWeb = window.location.origin + '/control';

      function formatImporte(importe: string | number) {
        const numero = parseFloat(importe as string);
        if (isNaN(numero)) {
          return '0,00';
        } else {
          const [entero, decimal] = numero.toFixed(2).split('.');
          return `${entero},${decimal}`;
        }
      }

      // Construcción del HTML para la tabla
      html += '<tr>';

      // 1 - IdReserva
      html += '<td>';
      if (Number(data.idReserva) === 1) {
        html += '<button type="button" class="btn btn-primary btn-sm">Client anual</button>';
      } else {
        html += data.idReserva + ' // ' + fechaReserva_formateada;
      }
      html += '</td>';

      // 2 - Importe
      html += `<td><strong>${formatImporte(data.importe)} €</strong></td>`;

      // 3 - Pagado
      html += '<td>';
      if (Number(data.idReserva) === 1) {
        html += '<button type="button" class="btn btn-success">SI</button>';
        html += '<p>Client anual</p>';
      } else {
        if (Number(data.processed) === 1) {
          html += '<button type="button" class="btn btn-success">SI</button>';
          html += `<p><a href="${urlWeb}/reserva/verificar-pagament/${data.id}"><strong>Verificar pagament</a></p>`;
        } else {
          html += '<button type="button" class="btn btn-danger">NO</button>';
          html += `<p><a href="${urlWeb}/reserva/verificar-pagament/${data.id}"><strong>Verificar pagament</a></p>`;
        }
      }
      html += '</td>';

      // 4 - Tipus de reserva
      html += `<td><strong>${tipoReserva2}</strong></td>`;

      // 5 - Neteja
      html += `<td>${limpieza2}</td>`;

      // 6 - Client i telefon
      html += '<td>';
      if (data.nombre) {
        html += `${data.nombre} // ${data.tel}`;
      } else {
        html += `${data.clientNom} ${data.clientCognom} // ${data.telefono}`;
      }
      html += '</td>';

      // 7 - Entrada (dia i hora)
      html += '<td>';
      if (dataEntradaAny === '1970') {
        html += 'Pendent';
      } else {
        html += `<strong>${dataEntrada2} // ${data.HoraEntrada}</strong>`;
      }
      html += '</td>';

      // 8 - Sortida (dia i hora)
      html += '<td>';
      if (dataSortidaAny === '1970') {
        html += 'Pendent';
      } else {
        html += `${dataSortida2} // ${data.HoraSortida}`;
      }
      html += '</td>';

      // 9 - Vehicle i matricula
      html += '<td>';
      html += data.modelo;
      if (data.matricula) {
        html += ` // ${data.matricula}`;
      } else {
        html += `<p><a href="${urlWeb}/reserva/modificar/vehicle/${data.id}" class="btn btn-secondary btn-sm" role="button" aria-pressed="true">Afegir matrícula</a></p>`;
      }
      if (data.numeroPersonas) {
        html += `<p> // ${data.numeroPersonas} personas</p>`;
      } else {
        html += ' // -';
      }
      html += '</td>';

      // 10 - Dades vol
      html += '<td>';
      if (!data.vuelo) {
        html += `<a href="${urlWeb}/reserva/modificar/vol/${data.id}" class="btn btn-secondary btn-sm" role="button" aria-pressed="true">Afegir vol</a>`;
      } else {
        html += data.vuelo;
      }
      html += '</td>';

      // 11 - CheckIn
      html += '<td>';
      if (data.checkIn === 5) {
        html += `<a href="${urlWeb}/reserva/fer/check-in/${data.id}" class="btn btn-secondary btn-sm" role="button" aria-pressed="true">Check-In</a>`;
      }
      html += '</td>';

      // 12 - Notes
      html += '<td>';
      if (!data.idReserva) {
        html += `<a href="${urlWeb}/reserva/modificar/nota/${data.id}" class="btn btn-info btn-sm" role="button" aria-pressed="true">Crear</a>`;
      } else if (data.idReserva && !data.notes) {
        html += `<a href="${urlWeb}/reserva/modificar/nota/${data.id}" class="btn btn-info btn-sm" role="button" aria-pressed="true">Crear</a>`;
      } else if (data.notes) {
        html += `<a href="${urlWeb}/reserva/info/nota/${data.id}" class="btn btn-danger btn-sm" role="button" aria-pressed="true">Veure</a>`;
      }
      html += '</td>';

      // 13 - Cercadors
      html += '<td>';
      if (Number(data.idReserva) === 1) {
        html += '-';
      } else {
        if (!data.idReserva) {
          html += `<a href="${urlWeb}/reserva/modificar/cercador/${data.id}" class="btn btn-warning btn-sm" role="button" aria-pressed="true">Alta</a>`;
        } else if (data.idReserva && !data.buscadores) {
          html += `<a href="${urlWeb}/reserva/modificar/cercador/${data.id}" class="btn btn-warning btn-sm" role="button" aria-pressed="true">Alta</a>`;
        } else {
          html += '<button type="button" class="btn btn-success btn-sm">Alta</button>';
        }
      }
      html += '</td>';

      // 14 - Email confirmacio
      html += '<td><button class="btn btn-success btn-sm obrir-finestra-btn" role="button" aria-pressed="true" data-id="' + data.id + '">Obrir</button></td>';

      html += '</tr>';

      // Agregar fila
      fila.innerHTML = html;
      tableBody.appendChild(fila);
    });
  } catch (error) {
    console.error('Error al cargar los datos:', error);
  }
};
