import { ApiErr, ApiOk, ApiResponse } from '../types/api';

export function isRecord(v: unknown): v is Record<string, unknown> {
  return typeof v === 'object' && v !== null;
}

export function isApiOk<T>(v: unknown): v is ApiOk<T> {
  return isRecord(v) && v.status === 'success' && typeof v.message === 'string' && 'data' in v;
}

export function isApiErr(v: unknown): v is ApiErr {
  return isRecord(v) && v.status === 'error' && typeof v.message === 'string';
}

export function parseApiResponse<T>(json: unknown): ApiResponse<T> {
  if (isApiOk<T>(json)) return json;
  if (isApiErr(json)) return json;
  return { status: 'error', code: 'BAD_RESPONSE', message: 'Respuesta inválida del servidor' };
}

export class ApiConflictError extends Error {
  public readonly code?: string;
  public readonly details?: unknown;

  constructor(message: string, code?: string, details?: unknown) {
    super(message);
    this.name = 'ApiConflictError';
    this.code = code;
    this.details = details;
  }
}

export class ApiRequestError extends Error {
  public readonly status: number;
  public readonly code?: string;
  public readonly details?: unknown;

  constructor(message: string, status: number, code?: string, details?: unknown) {
    super(message);
    this.name = 'ApiRequestError';
    this.status = status;
    this.code = code;
    this.details = details;
  }
}
