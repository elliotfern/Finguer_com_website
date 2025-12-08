export const actualizarEstadoReserva = async (id: number, nuevoEstado: string): Promise<void> => {
  const url = `${window.location.origin}/api/intranet/reserves/post/?type=update-estado`;

  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      id,
      estado_vehiculo: nuevoEstado,
    }),
  });

  if (!response.ok) {
    throw new Error(`Error al actualizar estado (HTTP ${response.status})`);
  }

  const resultado = await response.json();

  if (!resultado.success) {
    throw new Error(resultado.message || 'Error al actualizar estado');
  }
};
