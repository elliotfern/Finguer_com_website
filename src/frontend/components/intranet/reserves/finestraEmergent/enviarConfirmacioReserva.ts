type ApiResponse<T = unknown> = {
  status: 'success' | 'error' | 'partial';
  message: string;
  code?: string;
  data?: T;
  step?: number;
  warning?: boolean;
};

export const enviarConfirmacioReserva = async (id: string): Promise<ApiResponse> => {
  const url = `${window.location.origin}/api/intranet/email/get/?type=emailConfirmacioReserva&id=${encodeURIComponent(id)}`;

  const res = await fetch(url, { method: 'GET' });

  let json: ApiResponse;
  try {
    json = (await res.json()) as ApiResponse;
  } catch {
    throw new Error(`Respuesta no JSON (HTTP ${res.status})`);
  }

  if (!res.ok || json.status !== 'success') {
    throw new Error(json.message || `Error HTTP ${res.status}`);
  }

  return json;
};
