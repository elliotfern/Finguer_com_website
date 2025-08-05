import { enviarConfirmacioReserva } from './enviarConfirmacioReserva';

// Función para abrir la ventana emergente y posicionarla encima del botón
export const obrirFinestra = (event: MouseEvent, id: string): void => {
  const urlWeb = window.location.origin + '/control';
  const ventana = document.getElementById('ventanaEmergente') as HTMLElement;

  // Asegurarnos que boton es el botón con la clase y atributos data-*
  let boton = event.target as HTMLElement;
  while (boton && !boton.classList.contains('obrir-finestra-btn')) {
    boton = boton.parentElement as HTMLElement;
  }
  if (!boton) {
    console.error('Botón con clase "obrir-finestra-btn" no encontrado en el evento');
    return;
  }

  const btnConfirmacio = document.getElementById('enlace1') as HTMLButtonElement;

  if (btnConfirmacio) {
    // Restaurar el texto original del botón
    btnConfirmacio.textContent = 'Enviar confirmació email';
    btnConfirmacio.disabled = false;

    // Eliminar estilos de desactivado
    btnConfirmacio.style.cursor = 'pointer';
    btnConfirmacio.style.opacity = '1';

    // Cambiar clases
    btnConfirmacio.classList.remove('btn-success');
    btnConfirmacio.classList.add('btn-secondary');

    // Reemplazar botón para limpiar eventos antiguos
    const nuevoBtnConfirmacio = btnConfirmacio.cloneNode(true) as HTMLButtonElement;
    btnConfirmacio.parentNode?.replaceChild(nuevoBtnConfirmacio, btnConfirmacio);

    nuevoBtnConfirmacio.addEventListener('click', function (event) {
      event.preventDefault();
      enviarConfirmacioReserva(id);
    });
  }

  // ==== NUEVO: obtener info técnica desde atributos data-*
  const dispositiu = boton.getAttribute('data-dispositiu') || '—';
  const navegador = boton.getAttribute('data-navegador') || '—';
  const sistema = boton.getAttribute('data-sistema') || '—';
  const ip = boton.getAttribute('data-ip') || '—';

  const spanDispositiu = document.getElementById('dispositiu');
  const spanNavegador = document.getElementById('navegador');
  const spanSistema = document.getElementById('sistema_operatiu');
  const spanIp = document.getElementById('ip');

  if (spanDispositiu) spanDispositiu.innerHTML = `<strong>Dispositiu:</strong> ${dispositiu}`;
  if (spanNavegador) spanNavegador.innerHTML = `<strong>Navegador:</strong> ${navegador}`;
  if (spanSistema) spanSistema.innerHTML = `<strong>Sistema:</strong> ${sistema}`;
  if (spanIp) spanIp.innerHTML = `<strong>IP:</strong> ${ip}`;

  const enlace2 = document.getElementById('enlace2') as HTMLAnchorElement;
  const enlace3 = document.getElementById('enlace3') as HTMLAnchorElement;
  const enlace4 = document.getElementById('enlace4') as HTMLAnchorElement;

  if (enlace2) enlace2.href = `${urlWeb}/reserva/email/factura/${id}`;
  if (enlace3) enlace3.href = `${urlWeb}/reserva/modificar/reserva/${id}`;
  if (enlace4) enlace4.href = `${urlWeb}/reserva/eliminar/reserva/${id}`;

  if (boton && ventana) {
    // --- Mostrar ventana primero ---
    ventana.style.display = 'block';

    // --- Esperar un frame para que el navegador calcule tamaños ---
    requestAnimationFrame(() => {
      const botonRect = boton.getBoundingClientRect();
      const ventanaWidth = ventana.offsetWidth;
      const ventanaHeight = ventana.offsetHeight;

      let left = botonRect.left + botonRect.width / 2 - ventanaWidth / 2;
      if (left + ventanaWidth > window.innerWidth) {
        left = window.innerWidth - ventanaWidth - 10;
      }
      if (left < 10) {
        left = 10;
      }

      let top = botonRect.top + window.scrollY + botonRect.height + 10;
      if (top + ventanaHeight > window.innerHeight + window.scrollY) {
        top = botonRect.top + window.scrollY - ventanaHeight - 10;
      }
      if (top < window.scrollY) {
        top = window.scrollY + 10;
      }

      ventana.style.left = `${left}px`;
      ventana.style.top = `${top}px`;
    });
  }
};

// Función para cerrar la ventana emergente
export const tancarFinestra = (): void => {
  const ventana = document.getElementById('ventanaEmergente') as HTMLElement;
  if (ventana) {
    ventana.style.display = 'none';
  }
};
