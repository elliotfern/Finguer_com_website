import { apiUrl } from '../../config/globals';
import { fetchData } from '../../services/api/api';

interface ApiResponse {
  idReserva: string;
  fechaReserva: string;
  clientNom: string | null;
  clientCognom: string | null;
  telefono: string | null;
  dataSortida: string;
  HoraEntrada: string;
  HoraSortida: string;
  dataEntrada: string;
  matricula: string;
  modelo: string;
  vuelo: string;
  tipo: number;
  checkIn: number | null;
  checkOut: number | null;
  notes: string | null;
  limpiada: number;
  importe: string;
  id: number;
  processed: number;
  nombre: string;
  tel: string;
  limpieza: number;
}

interface PostRequest {
  email: string;
}

export const areaClientHistoricReserves = () => {
  document.addEventListener('DOMContentLoaded', () => {
    login();
  });
};

const login = async () => {
  // Función para obtener la cookie
  function getCookie(name: string): string | null {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop()?.split(';').shift() || null;
    return null;
  }

  // Obtener el valor de la cookie 'email'
  const email: string | null = getCookie('email');
  console.log(email);

  // Validación: si no se encuentra el email en las cookies, no hacer la solicitud
  if (!email) {
    console.log('No se encontró el email en las cookies');
    return;
  }

  const postData: PostRequest = { email: email };

  // Llamada a la API
  const data = await fetchData<ApiResponse[], PostRequest>(`${apiUrl}/area-client/reservas/?type=reservas&cliente=${email}`, 'POST', postData);

  // Verificación si no hay datos
  if (!data || !Array.isArray(data)) {
    const taula = document.getElementById('table-container');
    if (taula) {
      taula.innerHTML = '<p>No hay ninguna reserva</p>';
      return;
    }
  }

  let table = '<table class="table table-striped" id="pendents">';
  table += '<thead class="table-dark"><tr>' + '<th>Núm. Reserva / Fecha</th>' + '<th>Importe</th>' + '<th>Pagado</th>' + '<th>Tipo</th>' + '<th>Limpieza</th>' + '<th>Entrada &darr;</th>' + '<th>Salida</th>' + '<th>Vehículo</th>' + '<th>Factura</th>' + '</tr></thead>';
  table += '<tbody>';

  // Usando forEach para recorrer los datos de forma más sencilla
  if (data) {
    data.forEach((item) => {
      const tipoReserva2 = item.tipo === 1 ? 'Finguer Class' : item.tipo === 2 ? 'Gold Finguer Class' : '';

      const limpieza2 = item.limpieza === 1 ? 'Servicio de limpieza exterior' : item.limpieza === 2 ? 'Servicio de lavado exterior + aspirado tapicería interior' : item.limpieza === 3 ? 'Limpieza PRO' : '-';

      const html = `
          <tr>
            <td>${item.idReserva === '1' ? '<button type="button" class="btn btn-primary btn-sm">Client anual</button>' : item.idReserva + ' // ' + item.fechaReserva}</td>
            <td><strong>${item.importe} €</strong></td>
            <td>${item.processed === 1 ? '<button type="button" class="btn btn-success">SI</button>' : '<button type="button" class="btn btn-danger">NO</button>'}</td>
            <td><strong>${tipoReserva2}</strong></td>
            <td>${limpieza2}</td>
            <td>${'<strong>' + item.dataEntrada + ' // ' + item.HoraEntrada + '</strong>'}</td>
            <td>${item.dataSortida + ' // ' + item.HoraSortida}</td>
            <td>${item.modelo}${item.matricula ? ' // ' + item.matricula : ''}</td>
            ${item.processed === 1 ? `<td><button class="btn btn-primary btn-sm" role="button" aria-pressed="true" onClick="enviarFactura(${item.id});"><i class="bi bi-file-earmark-pdf"></i></button></td>` : '<td></td>'}
          </tr>
        `;

      table += html;
    });

    table += '</tbody></table>';
    const taula2 = document.getElementById('table-container');
    if (taula2) {
      taula2.innerHTML = table;
    }
  }
};

/*
// Definir el tipo de la respuesta de la API, en este caso la respuesta podría ser algo como { status: string, message: string }
interface FacturaResponse {
  status: string;
  message: string;
}

// Función para enviar la factura

async function enviarFactura(id: number): Promise<void> {
  try {
    // Construir la URL de la solicitud
    const url = `${window.location.origin}/api/area-client/reservas/?type=factura&cliente=${id}`;

    // Realizar la solicitud GET a la API utilizando fetchData
    const response = await fetchData<FacturaResponse, undefined>(url, 'GET');

    // Verificar la respuesta
    if (response) {
      if (response.status === 'success') {
        console.log('Factura enviada correctamente:', response.message);
      } else {
        console.error('Error al enviar la factura:', response.message);
      }
    }
  } catch (error) {
    // Manejo de errores
    console.error('Error al realizar la solicitud:', error);
  }
}
*/
