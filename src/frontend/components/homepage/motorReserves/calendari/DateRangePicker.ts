import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';
import { resetContadores } from '../formulari/ResetContadores';
import { schedulePressupost } from '../pressupost/schedulePressupost';
import { avisEspecialTancamentParking } from './avisEspecialTancamentParking';

interface FechaNoDisponible {
    dia: number;
    mes: number;
}

interface ConfiguracionReserva {
    ok: boolean;
    fechaMinima: string;
    fechaMaxima: string;
    fechasNoDisponibles: FechaNoDisponible[];
}

const CONFIGURACION_FALLBACK: ConfiguracionReserva = {
    ok: false,
    fechaMinima: (() => {
        const d = new Date();
        d.setDate(d.getDate() + 2);
        return d.toISOString().slice(0, 10);
    })(),
    fechaMaxima: '2027-12-31',
    fechasNoDisponibles: [
        { dia: 25, mes: 12 },
        { dia: 26, mes: 12 },
        { dia: 31, mes: 12 },
        { dia: 1, mes: 1 },
    ],
};

async function getConfiguracionReserva(): Promise<ConfiguracionReserva> {
    try {
        const res = await fetch('api/carro-compra/configuracion-reserva');
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = (await res.json()) as ConfiguracionReserva;
        if (!data.ok) throw new Error('Respuesta no ok');
        return data;
    } catch (err) {
        console.error(
            'No se pudo cargar la configuración de reserva, usando valores por defecto:',
            err
        );
        return CONFIGURACION_FALLBACK;
    }
}

export const DateRangePicker = async () => {
    const fechaReservaElement = document.querySelector(
        '#fecha_reserva'
    ) as HTMLElement;

    if (!fechaReservaElement) return;

    const config = await getConfiguracionReserva();

    const esFechaNoDisponible = (fecha: Date): boolean =>
        config.fechasNoDisponibles.some(
            (f) => fecha.getDate() === f.dia && fecha.getMonth() + 1 === f.mes
        );

    const onDayCreate = (
        _dObj: Date[],
        _dStr: string,
        _fp: flatpickr.Instance,
        dayElem: HTMLElement & { dateObj: Date }
    ) => {
        if (esFechaNoDisponible(dayElem.dateObj)) {
            dayElem.setAttribute(
                'title',
                'Este día no se admiten entradas ni salidas (parking cerrado para check-in/check-out).'
            );
            dayElem.classList.add('dia-cierre-especial');
        }
    };

    flatpickr(fechaReservaElement, {
        mode: 'range',
        altInput: true,
        altFormat: 'd/m/Y',
        dateFormat: 'Y-m-d',
        minDate: config.fechaMinima,
        maxDate: config.fechaMaxima,
        // No usamos `disable` aquí: en modo range, flatpickr impediría
        // seleccionar cualquier rango que "pase por encima" de estos días
        // (p. ej. entrada 24/12 -> salida 28/12). La regla real es que
        // estos días no pueden ser entrada NI salida, pero sí pueden
        // quedar dentro de una reserva más larga. Por eso se valida en
        // onChange, igual que hace el backend (ReglasReserva::validarRango).
        onDayCreate,
        locale: {
            firstDayOfWeek: 1,
            weekdays: {
                shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
                longhand: [
                    'Domingo',
                    'Lunes',
                    'Martes',
                    'Miércoles',
                    'Jueves',
                    'Viernes',
                    'Sábado',
                ],
            },
            months: {
                shorthand: [
                    'Ene',
                    'Feb',
                    'Mar',
                    'Abr',
                    'May',
                    'Jun',
                    'Jul',
                    'Ago',
                    'Sep',
                    'Oct',
                    'Nov',
                    'Dic',
                ],
                longhand: [
                    'Enero',
                    'Febrero',
                    'Marzo',
                    'Abril',
                    'Mayo',
                    'Junio',
                    'Julio',
                    'Agosto',
                    'Septiembre',
                    'Octubre',
                    'Noviembre',
                    'Diciembre',
                ],
            },
        },
        onChange: (selectedDates, _dateStr, instance) => {
            const avisoDiv = document.getElementById(
                'avis_especial'
            ) as HTMLElement | null;
            const detallesReserva = document.getElementById(
                'importeReserva'
            ) as HTMLElement | null;

            if (selectedDates.length === 2) {
                const [start, end] = selectedDates;

                if (esFechaNoDisponible(start) || esFechaNoDisponible(end)) {
                    instance.clear();
                    resetContadores();
                    avisEspecialTancamentParking(true);
                    if (detallesReserva) detallesReserva.style.display = 'none';
                    return;
                }

                if (avisoDiv) avisoDiv.style.display = 'none';
                if (detallesReserva) detallesReserva.style.display = 'block';
                schedulePressupost();
                return;
            }

            if (avisoDiv) avisoDiv.style.display = 'none';
            if (detallesReserva) detallesReserva.style.display = 'block';
            resetContadores();
        },
    });
};
