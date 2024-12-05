export interface Reserva {
    id: number;
    idReserva: number;
    fechaReserva: string;
    importe: string | number;
    processed: number;
    tipo: number;
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
    modelo: string;
    numeroPersonas: number;
    notes: string;
    vuelo: string;
    checkIn: number;
    buscadores: number;
}

export interface Comptador {
    numero: number;
}