import { enviarConfirmacioReserva } from './enviarConfirmacioReserva.js';

// Función para abrir la ventana emergente y posicionarla encima del botón
export const obrirFinestra = (event: MouseEvent, id: string): void => {
  const urlWeb = window.location.origin + '/control';
  const ventana = document.getElementById('ventanaEmergente') as HTMLElement;
  const boton = event.target as HTMLElement; // Botón que dispara el evento

  const btnConfirmacio = document.getElementById('enlace1') as HTMLButtonElement;
  //const btnFactura = document.getElementById('enlace2') as HTMLAnchorElement;

  if (btnConfirmacio) {
    // Restaurar el texto original del botón
    btnConfirmacio.textContent = 'Enviar confirmació email'; // Texto original
    btnConfirmacio.disabled = false; // Asegurarse de habilitar el botón

    // Eliminar estilos de desactivado
    btnConfirmacio.style.cursor = 'pointer'; // Restaurar el cursor
    btnConfirmacio.style.opacity = '1'; // Restaurar opacidad

    // Cambiar las clases de los botones
    btnConfirmacio.classList.remove('btn-success');
    btnConfirmacio.classList.add('btn-secondary');

    // Eliminar cualquier evento de clic previo
    const nuevoBtnConfirmacio = btnConfirmacio.cloneNode(true) as HTMLButtonElement;
    btnConfirmacio.parentNode?.replaceChild(nuevoBtnConfirmacio, btnConfirmacio);

    // Asociar el nuevo evento al botón
    nuevoBtnConfirmacio.addEventListener('click', function (event) {
      event.preventDefault(); // Evitar que el enlace cambie la URL
      enviarConfirmacioReserva(id); // Llamar a la función para ejecutar la acción
    });
  }

  // Configurar enlaces con el ID recibido
  const enlace2 = document.getElementById('enlace2') as HTMLAnchorElement;
  const enlace3 = document.getElementById('enlace3') as HTMLAnchorElement;
  const enlace4 = document.getElementById('enlace4') as HTMLAnchorElement;

  if (enlace2) enlace2.href = `${urlWeb}/reserva/email/factura/${id}`;
  if (enlace3) enlace3.href = `${urlWeb}/reserva/modificar/reserva/${id}`;
  if (enlace4) enlace4.href = `${urlWeb}/reserva/eliminar/reserva/${id}`;

  // Calcular la posición del botón y ajustar la ventana emergente
  if (boton && ventana) {
    const botonRect = boton.getBoundingClientRect();
    const ventanaWidth = ventana.offsetWidth;
    const ventanaHeight = ventana.offsetHeight;

    // Calcular posición horizontal
    let left = botonRect.left + botonRect.width / 2 - ventanaWidth / 2;
    if (left + ventanaWidth > window.innerWidth) {
      left = window.innerWidth - ventanaWidth - 10; // Ajustar margen derecho
    }
    if (left < 10) {
      left = 10; // Ajustar margen izquierdo
    }

    // Calcular posición vertical
    let top = botonRect.top + window.scrollY + botonRect.height + 10;
    if (top + ventanaHeight > window.innerHeight + window.scrollY) {
      top = botonRect.top + window.scrollY - ventanaHeight - 10; // Ajustar posición superior
    }
    if (top < window.scrollY) {
      top = window.scrollY + 10; // Ajustar posición superior mínima
    }

    // Aplicar posiciones ajustadas
    ventana.style.left = `${left}px`;
    ventana.style.top = `${top}px`;

    // Mostrar la ventana
    ventana.style.display = 'block';
  }
};

// Función para cerrar la ventana emergente
export const tancarFinestra = (): void => {
  const ventana = document.getElementById('ventanaEmergente') as HTMLElement;
  if (ventana) {
    ventana.style.display = 'none'; // Ocultar la ventana emergente
  }
};
