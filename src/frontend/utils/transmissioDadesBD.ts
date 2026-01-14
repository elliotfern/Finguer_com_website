import { Missatges } from '../i18n/traduccionsTextos';
import type { ApiResponse, ApiOk, ApiErr } from '../types/api';
import { missatgesBackend } from './missatgesBackend';
import { resetForm } from './resetForm';

// Comportamiento genérico en éxito
type SuccessBehavior = 'none' | 'hide' | 'disable';

function isApiResponse<T>(value: unknown): value is ApiResponse<T> {
  if (typeof value !== 'object' || value === null) return false;
  if (!('status' in value)) return false;

  const status = (value as { status?: unknown }).status;
  if (status !== 'success' && status !== 'error') return false;

  if (!('message' in value) || typeof (value as { message?: unknown }).message !== 'string') return false;

  if (status === 'success' && !('data' in value)) return false;

  return true;
}

function isApiSuccess<T>(value: ApiResponse<T>): value is ApiOk<T> {
  return value.status === 'success';
}

function isApiError<T>(value: ApiResponse<T>): value is ApiErr {
  return value.status === 'error';
}

export async function transmissioDadesDB<TData = unknown>(
  event: Event,
  tipus: string,
  formId: string,
  urlAjax: string,
  neteja?: boolean,
  successBehavior: SuccessBehavior = 'none'
): Promise<void> {
  event.preventDefault();

  const form = document.getElementById(formId) as HTMLFormElement | null;
  if (!form) return;

  // Form → JSON
  const formDataRaw = new FormData(form);
  const payload: Record<string, string> = {};

  formDataRaw.forEach((value, key) => {
    payload[key] = typeof value === 'string' ? value : value.name; // File -> name
  });

  const okMessageDiv = document.getElementById('okMessage');
  const okTextDiv = document.getElementById('okText');
  const errMessageDiv = document.getElementById('errMessage');
  const errTextDiv = document.getElementById('errText');
  if (!okMessageDiv || !okTextDiv || !errMessageDiv || !errTextDiv) return;

  try {
    const response = await fetch(urlAjax, {
      method: tipus,
      credentials: 'same-origin', // 🔴 cookies auth
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(payload),
    });

    const rawText = await response.text();

    let parsedUnknown: unknown = null;
    try {
      parsedUnknown = rawText ? JSON.parse(rawText) : null;
    } catch {
      parsedUnknown = null;
    }

    if (!isApiResponse<TData>(parsedUnknown)) {
      // Si el backend devolvió HTML/notice/etc.
      throw new Error('Respuesta inválida del servidor');
    }

    const parsed = parsedUnknown; // ya es ApiResponse<TData>

    if (response.ok && isApiSuccess(parsed)) {
      missatgesBackend({
        tipus: 'success',
        missatge: parsed.message || Missatges.success.default,
        contenidor: okMessageDiv,
        text: okTextDiv,
        altreContenidor: errMessageDiv,
      });

      if (successBehavior === 'hide') {
        form.hidden = true;
        history.replaceState({}, document.title, window.location.pathname);
      } else if (successBehavior === 'disable') {
        form
          .querySelectorAll<HTMLElement>('input,select,textarea,button,[contenteditable],trix-editor')
          .forEach((el) => el.setAttribute('disabled', 'true'));
        history.replaceState({}, document.title, window.location.pathname);
      } else if (neteja) {
        resetForm(formId);
      }

      form.dispatchEvent(new CustomEvent<ApiResponse<TData>>('form:success', { detail: parsed }));
      return;
    }

    // Error controlado del backend (o HTTP no OK)
    const errorsList =
      isApiError(parsed) && parsed.errors && parsed.errors.length > 0
        ? `<ul>${parsed.errors.map((e) => `<li>${e}</li>`).join('')}</ul>`
        : response.ok
          ? `<p>${Missatges.error.default}</p>`
          : '';

    const errorHtml = `
      ${parsed.message ? `<p>${parsed.message}</p>` : ''}
      ${errorsList}
    `;

    missatgesBackend({
      tipus: 'error',
      missatge: errorHtml,
      contenidor: errMessageDiv,
      text: errTextDiv,
      altreContenidor: okMessageDiv,
    });
  } catch (error) {
    const message = error instanceof Error ? error.message : Missatges.error.xarxa;

    missatgesBackend({
      tipus: 'error',
      missatge: message,
      contenidor: errMessageDiv,
      text: errTextDiv,
    });
  }
}
