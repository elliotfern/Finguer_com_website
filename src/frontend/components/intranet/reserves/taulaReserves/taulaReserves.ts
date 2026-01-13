import { apiUrl, webUrl } from '../../../../config/globals';
import { ApiResponse } from '../../../../types/api';
import { Reserva } from '../../../../types/interfaces';
import { isApiOk } from '../../../../utils/api';
import { isAdmin } from '../../auth/store';
import { actualizarEstadoReserva } from './actualitzarEstatReserva';
import { ComptadorReserves, comptadorReserves } from './comptadorReserves';

function formatEstadoReservaHtml(estado: string): string {
  switch (estado) {
    case 'pendiente':
      return '<span class="badge bg-warning text-dark">Pendent de pagament</span>';
    case 'pago_oficina':
      return '<span class="badge bg-info text-dark">Pagament a oficina</span>';
    case 'pagada':
      return '<span class="badge bg-success">Pagada</span>';
    case 'cancelada':
      return '<span class="badge bg-danger">Cancel·lada</span>';
    case 'anual':
      return '<span class="badge bg-primary">Client anual</span>';
    default:
      return `<span class="badge bg-secondary">${estado}</span>`;
  }
}

type FacturaInfo = { status: string; id: number };

type PagoManualData = {
  reserva?: unknown;
  pago?: unknown;
  factura?: FacturaInfo | null;
  envio_factura?: unknown;
};

type PagoManualResponse = {
  status: 'success' | 'error';
  message?: string;
  code?: string;
  data?: PagoManualData;
};

export async function confirmarPagoManual(reservaId: number): Promise<PagoManualData> {
  const res = await fetch(`${apiUrl}/factures/post/confirmar-pago-manual/?type=pagoManual`, {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ reserva_id: reservaId }),
  });

  const json = (await res.json()) as PagoManualResponse;

  if (!res.ok || json.status !== 'success') {
    throw new Error(json.message || json.code || 'Error confirmando pago manual');
  }

  return json.data ?? {};
}

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
      <th>Notes</th>
      <th>Import</th>
      <th>Factura</th>
      <th>Veri*factu</th>
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

export const carregarDadesTaulaReserves = async (estatParking: string, tipo?: string): Promise<void> => {
  try {
    let url = '';
    const tipo_int = tipo;
    if (tipo_int) {
      url = `${apiUrl}/intranet/reserves/get/?type=reserves&estado_vehiculo=${encodeURIComponent(estatParking)}&tipo=${encodeURIComponent(tipo_int)}`;
    } else {
      url = `${apiUrl}/intranet/reserves/get/?type=reserves&estado_vehiculo=${encodeURIComponent(estatParking)}`;
    }

    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const payload = (await response.json()) as ApiResponse<{
      counts: ComptadorReserves;
      rows: Reserva[];
      hasRows: boolean;
    }>;

    if (!isApiOk(payload)) {
      throw new Error(`${payload.code ?? 'API_ERROR'}: ${payload.message}`);
    }

    const datos: Reserva[] = payload.data.rows ?? [];
    const counts: ComptadorReserves | undefined = payload.data.counts;

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

    const urlWeb = `${webUrl}/control`;

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
      if (data.estado === 'anual') {
        html += '<button type="button" class="btn btn-primary btn-sm">Client anual</button>';
      } else {
        html += data.localizador + ' // ' + fechaReserva_formateada;
      }
      html += '</td>';

      // 2 . Notes
      html += '<td>';
      if (data.localizador && !data.notes) {
        html += `<a href="${urlWeb}/reserva/modificar/nota/${data.id}" class="btn btn-info btn-sm" role="button" aria-pressed="true">Crear</a>`;
      } else if (data.notes) {
        html += `<button class="btn btn-danger btn-sm" type="button" role="button" aria-pressed="true">${data.notes}</button>`;
      }
      html += '</td>';

      // 2 - Importe
      if (Number(data.canal) === 5) {
        html += `<td> - </td>`;
      } else {
        html += `<td><strong>${formatImporte(data.importe)} €</strong></td>`;
      }

      // 3 - FACTURA PDF - por rol
      html += '<td>';

      const canal = Number(data.canal);
      const hasFactura = !!(data.factura_id && data.factura_numero && data.factura_serie);

      if (hasFactura) {
        // Mostrar número de factura como enlace
        html += `<a href="#" class="btn btn-outline-secondary btn-sm factura-pdf" data-id="${data.id}">
          ${data.factura_numero}/${data.factura_serie}
        </a>`;
      } else if (canal === 5) {
        html += '-';
      } else {
        // ✅ NO hay factura y NO es canal 5
        // Solo admin puede ver/usar este botón
        if (isAdmin()) {
          html += `<button type="button"
      class="btn btn-success btn-sm confirmar-pago-manual"
      data-id="${data.id}">
      Cobrar y emitir factura
    </button>`;
        } else {
          html += '-';
        }
      }

      html += '</td>';

      // 3 - VERIFACTU
      if (Number(data.canal) === 5) {
        html += `<td> - </td>`;
      } else {
        html += `<td><a href="#" class="btn btn-outline-secondary btn-sm">NO</a></td>`;
      }

      // 3 - Pagado
      html += '<td>';

      // === Cálculo antigüedad reserva ===
      const rawFecha = data.fecha_reserva as string; // ej: "2025-10-21 13:30:47"
      const fechaReserva = new Date(rawFecha.replace(' ', 'T')); // "2025-10-21T13:30:47"
      const ahora = new Date();

      const MS_PER_DAY = 1000 * 60 * 60 * 24;
      const diffDays = Math.floor((ahora.getTime() - fechaReserva.getTime()) / MS_PER_DAY);

      // Cambia 45 por el umbral que prefieras (40, 60…)
      const MAX_DIAS_VERIFICACION = 31;
      const puedeVerificar = diffDays <= MAX_DIAS_VERIFICACION;
      const estadoHtml = formatEstadoReservaHtml(data.estado);

      if (data.canal === '5') {
        html += `<p>${estadoHtml}</p>`;
      } else {
        if (Number(data.processed) === 1) {
          html += `<p><button type="button" class="btn btn-success">SI</button></p>
          <p>${estadoHtml}</p>`;

          // SOLO mostramos el enlace si la reserva aún está dentro del plazo “seguro”
          if (puedeVerificar) {
            html += `<p><a href="${urlWeb}/reserva/verificar-pagament/${data.id}"><strong>Verificar pagament</strong></a></p>`;
          }
        } else if (Number(data.canal) === 3) {
          html += `<p><button type="button" class="btn btn-danger">NO</button></p>
          <p>${estadoHtml}</p>`;
        } else {
          html += `<p><button type="button" class="btn btn-danger">NO</button></p>
          <p>${estadoHtml}</p>`;

          if (puedeVerificar) {
            html += `<p><a href="${urlWeb}/reserva/verificar-pagament/${data.id}"><strong>Verificar pagament</strong></a></p>`;
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
            await carregarDadesTaulaReserves(estatParking, tipo_int);
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
            await carregarDadesTaulaReserves(estatParking, tipo_int);
          } catch (error) {
            console.error('Error al hacer check-out:', error);
            alert('Error al hacer check-out');
          }
        });
      }

      // Agregar evento click para generar y mostrar PDF
      const btnFacturaPdf = fila.querySelector('.factura-pdf') as HTMLAnchorElement | null;

      if (btnFacturaPdf) {
        btnFacturaPdf.addEventListener('click', async (e) => {
          e.preventDefault();

          const reserva_id = btnFacturaPdf.getAttribute('data-id');
          if (!reserva_id) return;

          btnFacturaPdf.classList.add('disabled');
          btnFacturaPdf.textContent = 'Generando…';

          try {
            const response = await fetch(`${apiUrl}/factures/post/?type=emitir-factura`, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ reserva_id }),
            });

            const payload: {
              status?: string;
              message?: string;
              data?: { pdf_url?: string };
            } = await response.json();

            if (payload.status === 'success') {
              const pdfUrl = payload.data?.pdf_url?.replace(/\\\//g, '/');
              if (pdfUrl) {
                window.location.href = pdfUrl; // ✅ abre el PDF en la misma pestaña
                return;
              }
              alert('No se pudo obtener la URL del PDF.');
            } else {
              alert(payload.message || 'Error al generar la factura.');
            }
          } catch (err) {
            console.error(err);
            alert('Hubo un error al generar la factura.');
          } finally {
            btnFacturaPdf.classList.remove('disabled');
            btnFacturaPdf.textContent = `${data.factura_numero}/${data.factura_serie}`; // o restaura como quieras
          }
        });
      }

      // Botón: Confirmar pago manual + generar factura
      const btnConfirmarPago = fila.querySelector('.confirmar-pago-manual') as HTMLButtonElement | null;

      if (btnConfirmarPago) {
        btnConfirmarPago.addEventListener('click', async () => {
          const reservaId = Number(btnConfirmarPago.getAttribute('data-id') ?? 0);
          if (!reservaId) return;

          const ok = confirm('¿Confirmar pago manual y generar factura?');
          if (!ok) return;

          btnConfirmarPago.disabled = true;
          const oldText = btnConfirmarPago.textContent ?? '';
          btnConfirmarPago.textContent = 'Procesando...';

          try {
            const result = await confirmarPagoManual(reservaId);

            const facturaId = result.factura?.id ?? null;
            alert(facturaId ? `OK. Factura generada (ID ${facturaId}).` : 'OK. Pago confirmado.');

            // ✅ refrescar tabla para que aparezca el número de factura y desaparezca el botón
            await carregarDadesTaulaReserves(estatParking, tipo_int);
          } catch (e: unknown) {
            const msg = e instanceof Error ? e.message : 'Error desconocido';
            alert(`Error: ${msg}`);
            btnConfirmarPago.disabled = false;
            btnConfirmarPago.textContent = oldText;
          }
        });
      }
    });
  } catch (error) {
    console.error('Error al cargar los datos:', error);
  }
};
