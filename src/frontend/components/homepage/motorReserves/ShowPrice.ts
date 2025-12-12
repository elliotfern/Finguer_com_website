// ShowPrice.ts
import { scheduleCotizar } from './scheduleCotizar';

export const showPrice = () => {
  const tipoReservaElement = document.getElementById('tipo_reserva') as HTMLSelectElement | null;
  const limpiezaElement = document.getElementById('limpieza') as HTMLInputElement | null;

  const seguroSiElement = document.getElementById('seguroSi') as HTMLInputElement | null;
  const seguroNoElement = document.getElementById('seguroNo') as HTMLInputElement | null;

  const horaEntradaElement = document.getElementById('horaEntrada') as HTMLInputElement | null;
  const horaSalidaElement = document.getElementById('horaSalida') as HTMLInputElement | null;

  if (tipoReservaElement && limpiezaElement && seguroSiElement && seguroNoElement && horaEntradaElement && horaSalidaElement) {
    tipoReservaElement.addEventListener('change', () => scheduleCotizar());
    limpiezaElement.addEventListener('change', () => scheduleCotizar());
    seguroSiElement.addEventListener('change', () => scheduleCotizar());
    seguroNoElement.addEventListener('change', () => scheduleCotizar());
    horaEntradaElement.addEventListener('change', () => scheduleCotizar());
    horaSalidaElement.addEventListener('change', () => scheduleCotizar());
  }
};
