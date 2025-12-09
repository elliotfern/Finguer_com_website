import { Reserva } from '../../../../types/interfaces';
import { actualizarEstadoReserva } from './actualitzarEstatReserva';
import { ComptadorReserves, comptadorReserves } from './comptadorReserves';

// Crea la tabla completa (div -> table -> thead -> tbody) si no existe y devuelve el <tbody>
const getOrCreateTableBody = (): HTMLTableSectionElement => {
  // Contenedor donde se pintará la tabla
  const container = document.getElementById('contenidorTaulaReserves');
  if (!container) {
    throw new Error("No se ha encontrado el contenedor con id 'contenidorTaulaReserves'");
  }

  // ¿Ya existe la tabla?
  let table = container.querySelector('#taulaReserves') as HTMLTableElement | null;
  let tbody: HTMLTableSectionElement | null = null;

  if (!table) {
    // Crear estructura: div.table-responsive > table#taulaReserves > thead > tbody
    const divResponsive = document.createElement('div');
    divResponsive.className = 'table-responsive';

    table = document.createElement('table');
    table.className = 'table table-striped';
    table.id = 'taulaReserves';

    const thead = document.createElement('thead');
    thead.className = 'table-dark';

    const headerRow = document.createElement('tr');
    headerRow.innerHTML = `
      <th>Núm. Comanda // data</th>
      <th>Import</th>
      <th>Factura</th>
      <th>Pagat</th>
      <th>Canal</th>
      <th>Tipus</th>
      <th>Neteja</th>
      <th>Client // tel.</th>
      <th>Entrada</th>
      <th>Sortida</th>
      <th>Dades Vehicle</th>
      <th>Vol tornada</th>
      <th>Estat reserva</th>
      <th>Notes</th>
      <th>Opcions</th>
    `;
    thead.appendChild(headerRow);

    tbody = document.createElement('tbody');

    table.appendChild(thead);
    table.appendChild(tbody);
    divResponsive.appendChild(table);
    container.appendChild(divResponsive);
  } else {
    // Si ya existe, simplemente obtenemos el tbody
    tbody = table.querySelector('tbody');
    if (!tbody) {
      tbody = document.createElement('tbody');
      table.appendChild(tbody);
    }
  }

  return tbody;
};

export const carregarDadesTaulaReserves = async (estatParking: string): Promise<void> => {
  try {
    const url = `${window.location.origin}/api/intranet/reserves/get/?type=reserves&estado_vehiculo=${estatParking}`;

    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const payload = (await response.json()) as {
      counts?: ComptadorReserves;
      rows?: Reserva[];
      hasRows?: boolean;
    };

    const datos: Reserva[] = payload.rows || [];
    const counts: ComptadorReserves | undefined = payload.counts;

    // 🔢 Actualizar contador de reservas
    comptadorReserves(estatParking, counts);

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

    // Obtener (o crear) la tabla y su tbody desde TS
    const tableBody = getOrCreateTableBody();
    // Limpiar el cuerpo de la tabla antes de agregar nuevos datos
    tableBody.innerHTML = '';

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

    datos.forEach((data: Reserva) => {
      const fila = document.createElement('tr');
      let html = '';

      // a) Fecha reserva
      const fechaReservaString = data.fecha_reserva;
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

      // Construcción del HTML para la fila
      html += '<tr>';

      // 1 - IdReserva
      html += '<td>';
      if (Number(data.localizador) === 1) {
        html += '<button type="button" class="btn btn-primary btn-sm">Client anual</button>';
      } else {
        html += data.localizador + ' // ' + fechaReserva_formateada;
      }
      html += '</td>';

      // 2 - Importe
      html += `<td><strong>${formatImporte(data.importe)} €</strong></td>`;

      // 3 - FACTURA PDF
      html += '<td>';

      if (data.factura_id && data.factura_numero && data.factura_serie) {
        // URL al endpoint que genera / sirve el PDF
        const urlFacturaPdf = `${window.location.origin}/api/factures/pdf/?type=factura-pdf&id=${data.factura_id}`;

        // Muestra número de factura como enlace
        html += `<a href="${urlFacturaPdf}" target="_blank" class="btn btn-outline-secondary btn-sm">
            ${data.factura_numero}/${data.factura_serie}
           </a>`;
      } else {
        html += '-';
      }

      html += '</td>';

      // 3 - Pagado
      html += '<td>';

      if (Number(data.localizador) === 1) {
        html += '<button type="button" class="btn btn-success">SI</button>';
        html += '<p>Client anual</p>';
      } else {
        // Calcular si la reserva tiene más de 2 meses
        const fechaReserva = new Date(data.fecha_reserva);
        const ahora = new Date();

        // Diferencia en milisegundos (Date -> number con getTime())
        const diffMs = ahora.getTime() - fechaReserva.getTime();

        // Aproximación de meses: 30 días por mes
        const diffMeses = diffMs / (1000 * 60 * 60 * 24 * 30);
        const esAntigua = diffMeses > 2;

        if (Number(data.processed) === 1) {
          html += '<button type="button" class="btn btn-success">SI</button>';

          // Sólo mostrar enlace si NO es antigua
          if (!esAntigua) {
            html += `<p><a href="${urlWeb}/reserva/verificar-pagament/${data.id}">
        <strong>Verificar pagament</strong></a></p>`;
          }
        } else if (Number(data.canal) === 3) {
          html += '<button type="button" class="btn btn-danger">NO</button>';
        } else {
          html += '<button type="button" class="btn btn-danger">NO</button>';

          // Sólo mostrar enlace si NO es antigua
          if (!esAntigua) {
            html += `<p><a href="${urlWeb}/reserva/verificar-pagament/${data.id}">
        <strong>Verificar pagament</strong></a></p>`;
          }
        }
      }

      html += '</td>';

      // 4 - CANAL
      html += '<td>';
      if (Number(data.canal) === 1) {
        html += 'Web';
      } else if (Number(data.canal) === 2) {
        html += `Cercador`;
      } else if (Number(data.canal) === 3) {
        html += 'Telèfon';
      } else if (Number(data.canal) === 5) {
        html += 'Client anual';
      } else {
        html += `Altres</p>`;
      }

      html += '</td>';

      // 4 - Tipus de reserva
      html += `<td><strong>${tipo}</strong></td>`;

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

      // 7 - Entrada (dia i hora) + 8 - Sortida (dia i hora)

      // Construimos primero los textos base (sin strong)
      let entradaContent = '';
      if (dataEntradaAny === '1970') {
        entradaContent = 'Pendent';
      } else {
        entradaContent = `${dataEntrada2} // ${data.HoraEntrada}`;
      }

      let sortidaContent = '';
      if (dataSortidaAny === '1970') {
        sortidaContent = 'Pendent';
      } else {
        sortidaContent = `${dataSortida2} // ${data.HoraSortida}`;
      }

      // Aplicar el <strong> según el estado del vehículo
      if (data.estado_vehiculo === 'pendiente_entrada') {
        // Resaltar la ENTRADA
        entradaContent = `<strong>${entradaContent}</strong>`;
      } else if (data.estado_vehiculo === 'dentro') {
        // Resaltar la SORTIDA
        sortidaContent = `<strong>${sortidaContent}</strong>`;
      }
      // si es "salido", no se aplica strong en ninguno

      // Pintar las celdas
      html += `<td>${entradaContent}</td>`;
      html += `<td>${sortidaContent}</td>`;

      // 9 - Vehicle i matricula
      html += '<td>';
      html += data.vehiculo;
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

      // 11 - CheckIn // CheckOut (versión AJAX)
      html += '<td>';
      if (data.estado_vehiculo === 'pendiente_entrada') {
        html += `<button type="button" class="btn btn-primary btn-sm js-check-in" data-id="${data.id}">Check-In</button>`;
      } else if (data.estado_vehiculo === 'dentro') {
        html += `<button type="button" class="btn btn-secondary btn-sm js-check-out" data-id="${data.id}">Check-out</button>`;
      } else if (data.estado_vehiculo === 'salido') {
        html += `Salido`;
      }
      html += '</td>';

      // 12 - Notes
      html += '<td>';
      if (data.localizador && !data.notes) {
        html += `<a href="${urlWeb}/reserva/modificar/nota/${data.id}" class="btn btn-info btn-sm" role="button" aria-pressed="true">Crear</a>`;
      } else if (data.notes) {
        html += `/${data.notes}`;
      }
      html += '</td>';

      // 14 - Email confirmacio
      html += `<td><button class="btn btn-success btn-sm obrir-finestra-btn" role="button" aria-pressed="true" data-id="${data.id}">Obrir</button></td>`;

      html += '</tr>';

      fila.innerHTML = html;
      tableBody.appendChild(fila);

      // Enganchar eventos a los botones de Check-In / Check-Out
      const btnCheckIn = fila.querySelector('.js-check-in') as HTMLButtonElement | null;
      const btnCheckOut = fila.querySelector('.js-check-out') as HTMLButtonElement | null;

      if (btnCheckIn) {
        btnCheckIn.addEventListener('click', async () => {
          try {
            await actualizarEstadoReserva(data.id, 'dentro');
            await carregarDadesTaulaReserves(estatParking);
          } catch (error) {
            console.error('Error al hacer check-in:', error);
            alert('Error al hacer check-in');
          }
        });
      }

      if (btnCheckOut) {
        btnCheckOut.addEventListener('click', async () => {
          try {
            await actualizarEstadoReserva(data.id, 'salido');
            await carregarDadesTaulaReserves(estatParking);
          } catch (error) {
            console.error('Error al hacer check-out:', error);
            alert('Error al hacer check-out');
          }
        });
      }
    });
  } catch (error) {
    console.error('Error al cargar los datos:', error);
  }
};
