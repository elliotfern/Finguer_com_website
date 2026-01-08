export type ApiOk<T> = {
  status: 'success';
  message: string;
  data: T;
};

export type ApiErr = {
  status: 'error';
  message: string;
  errors?: string[];
  code?: string;
};

export type ApiResponse<T> = ApiOk<T> | ApiErr;