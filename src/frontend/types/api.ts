export type ApiOk<T> = { status: 'success'; message: string; data: T };
export type ApiErr = { status: 'error'; code?: string; message: string };
export type ApiResponse<T> = ApiOk<T> | ApiErr;
