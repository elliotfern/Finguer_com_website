import { ApiOk, ApiResponse } from '../types/api';

export function isApiOk<T>(r: ApiResponse<T>): r is ApiOk<T> {
  return r.status === 'success';
}
