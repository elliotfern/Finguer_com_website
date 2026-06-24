import { avisEspecialTancamentParking } from './avisEspecialTancamentParking';

interface HorasDisponiblesResponse {
    ok: boolean;
    tipoReserva: string;
    fecha: string;
    horas: string[];
    avisoHorarioEspecial: boolean;
}

async function fetchHorasDisponibles(
    tipoReserva: string,
    fecha: string
): Promise<HorasDisponiblesResponse | null> {
    try {
        const params = new URLSearchParams({ tipoReserva, fecha });
        const res = await fetch(
            `api/carro-compra/horas-disponibles?${params.toString()}`
        );
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = (await res.json()) as HorasDisponiblesResponse;
        if (!data.ok) throw new Error('Respuesta no ok');
        return data;
    } catch (err) {
        console.error('No se pudieron cargar las horas disponibles:', err);
        return null;
    }
}

export const seleccionaHoraTipoReserva = () => {
    const tipoReservaSelect = document.getElementById(
        'tipo_reserva'
    ) as HTMLSelectElement;
    const horaEntradaSelect = document.getElementById(
        'horaEntrada'
    ) as HTMLSelectElement;
    const horaSalidaSelect = document.getElementById(
        'horaSalida'
    ) as HTMLSelectElement;
    const fechaReservaElement = document.getElementById(
        'fecha_reserva'
    ) as HTMLInputElement | null;

    let fechaInicioStr: string | undefined;
    let fechaFinStr: string | undefined;

    // El aviso debe mostrarse si CUALQUIERA de las dos fechas (entrada o salida)
    // es un día con horario especial.
    let avisoEntrada = false;
    let avisoSalida = false;

    const actualizarAviso = () => {
        avisEspecialTancamentParking(avisoEntrada || avisoSalida);
    };

    const llenarSelect = (select: HTMLSelectElement, horas: string[]) => {
        select.innerHTML =
            '<option selected value="">Selecciona una hora:</option>';
        horas.forEach((hora) => {
            const option = document.createElement('option');
            option.value = hora;
            option.textContent = hora;
            select.appendChild(option);
        });
    };

    const actualizarHorasEntrada = async (fecha: string) => {
        const tipo = tipoReservaSelect.value;
        if (!tipo || !fecha) return;

        const data = await fetchHorasDisponibles(tipo, fecha);
        if (!data) return;

        llenarSelect(horaEntradaSelect, data.horas);
        avisoEntrada = data.avisoHorarioEspecial;
        actualizarAviso();
    };

    const actualizarHorasSalida = async (fecha: string) => {
        const tipo = tipoReservaSelect.value;
        if (!tipo || !fecha) return;

        const data = await fetchHorasDisponibles(tipo, fecha);
        if (!data) return;

        llenarSelect(horaSalidaSelect, data.horas);
        avisoSalida = data.avisoHorarioEspecial;
        actualizarAviso();
    };

    // Inicializar a partir del valor actual del input de fechas
    if (fechaReservaElement && fechaReservaElement.value) {
        const fechas = fechaReservaElement.value.split(' to ');
        fechaInicioStr = fechas[0];
        fechaFinStr = fechas[1];
    }

    if (fechaInicioStr) actualizarHorasEntrada(fechaInicioStr);
    if (fechaFinStr) actualizarHorasSalida(fechaFinStr);

    // Cambiar las horas según el tipo de reserva
    tipoReservaSelect.addEventListener('change', () => {
        if (fechaInicioStr) actualizarHorasEntrada(fechaInicioStr);
        if (fechaFinStr) actualizarHorasSalida(fechaFinStr);
    });

    // Cambiar las horas según las fechas seleccionadas
    if (fechaReservaElement) {
        fechaReservaElement.addEventListener('change', () => {
            if (!fechaReservaElement.value) return;

            const fechas = fechaReservaElement.value.split(' to ');
            const nuevaFechaInicio = fechas[0];
            const nuevaFechaFin = fechas[1];

            if (nuevaFechaInicio !== fechaInicioStr) {
                fechaInicioStr = nuevaFechaInicio;
                actualizarHorasEntrada(fechaInicioStr);
            }

            if (nuevaFechaFin !== fechaFinStr) {
                fechaFinStr = nuevaFechaFin;
                actualizarHorasSalida(fechaFinStr);
            }
        });
    }
};
