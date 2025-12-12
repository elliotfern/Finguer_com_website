export const handleClickPagament = (): void => {
  const session = localStorage.getItem('carro_session');

  if (!session) {
    const msg = document.getElementById('mensaje_error');
    if (msg) msg.textContent = 'No se ha podido iniciar el pago: carrito no encontrado. Vuelve a seleccionar la reserva.';
    return;
  }

  // Importante: NO recalculamos ni guardamos nada aquí.
  // El snapshot ya está guardado en BD por /api/carro-compra/cotizar.
  window.location.href = `/pago/${encodeURIComponent(session)}`;
};
