export interface Reserva {
  id: number;
  localizador: number;
  fecha_reserva: string;
  importe: string | number;
  processed: number;
  tipo: string;
  limpieza: number;
  nombre?: string;
  tel?: string;
  clientNom?: string;
  clientCognom?: string;
  telefono?: string;
  dataEntrada: string;
  dataSortida: string;
  HoraEntrada: string;
  HoraSortida: string;
  matricula: string;
  vehiculo: string;
  numeroPersonas: number;
  notes: string;
  vuelo: string;
  estado_vehiculo: string;
  estado: string;
  canal: number;
  factura_id?: number | null;
  factura_numero?: string | null;
  factura_serie?: string | null;
}

export interface Comptador {
  numero: number;
}

export interface PaymentData {
  precioTotal: number;
  costeSeguro: number;
  precioReserva: number;
  costeIva: number;
  precioSubtotal: number;
  costoLimpiezaSinIva: number;
  fechaEntrada: string;
  fechaSalida: string;
  horaEntrada: string;
  horaSalida: string;
  limpieza: string;
  tipoReserva: string;
  diasReserva: number;
  seguroCancelacion: string;
  tipoLimpieza: string;
}

export interface ApiRespostaRedSys {
  status: string;
  params: string;
  signature: string;
  idReserva: string;
}
