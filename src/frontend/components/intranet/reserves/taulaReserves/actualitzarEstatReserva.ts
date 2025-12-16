import { ApiConflictError, ApiRequestError, parseApiResponse } from '../../../../utils/api';

type UpdateEstadoData = {
  id: number;
  estado_vehiculo: string;
  previous_estado_vehiculo?: string;
};

export const actualizarEstadoReserva = async (id: number, nuevoEstado: string): Promise<UpdateEstadoData> => {
  const url = `${window.location.origin}/api/intranet/reserves/post/?type=update-estado`;

  const response = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ id, estado_vehiculo: nuevoEstado }),
  });

  const json: unknown = await response.json().catch(() => null);

  if (!response.ok) {
    const parsed = parseApiResponse<UpdateEstadoData>(json);

    if (response.status === 409) {
      if (parsed.status === 'error') {
        throw new ApiConflictError(parsed.message, parsed.code, parsed.details);
      }
      throw new ApiConflictError('Conflicto al actualizar el estado');
    }

    if (parsed.status === 'error') {
      throw new ApiRequestError(parsed.message, response.status, parsed.code, parsed.details);
    }

    throw new ApiRequestError(`Error al actualizar estado (HTTP ${response.status})`, response.status);
  }

  const parsed = parseApiResponse<UpdateEstadoData>(json);

  if (parsed.status === 'error') {
    throw new ApiRequestError(parsed.message, response.status, parsed.code, parsed.details);
  }

  return parsed.data;
};

export { ApiConflictError, ApiRequestError };
