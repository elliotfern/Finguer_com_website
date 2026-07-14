import { API_URL } from '../../config/environment';
import { creacioReserva } from './creacioReserva';

interface SchemaFieldError {
    label: string;
    messages: string[];
}

export const creacioDadesUsuaris = async (
    idReserva: string
): Promise<{ status: string; message: string }> => {
    const url = `${API_URL}/alta-client`;

    const formData = {
        nombre:
            (document.getElementById('nombre') as HTMLInputElement)?.value ||
            '',
        telefono:
            (document.getElementById('telefono') as HTMLInputElement)?.value ||
            '',
        email:
            (document.getElementById('email') as HTMLInputElement)?.value || '',
        empresa:
            (document.getElementById('empresa') as HTMLInputElement)?.value ||
            '',
        nif: (document.getElementById('nif') as HTMLInputElement)?.value || '',
        direccion:
            (document.getElementById('direccion') as HTMLInputElement)?.value ||
            '',
        ciudad:
            (document.getElementById('ciudad') as HTMLInputElement)?.value ||
            '',
        codigo_postal:
            (document.getElementById('codigo_postal') as HTMLInputElement)
                ?.value || '',
        pais:
            (document.getElementById('pais') as HTMLInputElement)?.value || '',
    };

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData),
        });

        const data = await response.json();

        document
            .querySelectorAll('.invalid-feedback')
            .forEach((el) => (el.textContent = ''));
        document
            .querySelectorAll('.form-control')
            .forEach((el) => el.classList.remove('is-invalid'));

        // Errores de validación por campo (422): se procesan ANTES del
        // chequeo genérico de response.ok, porque 422 no es "ok" para fetch.
        if (data.status === 'error' && data.errors) {
            for (const [field, fieldError] of Object.entries(
                data.errors as Record<string, SchemaFieldError>
            )) {
                const errorDiv = document.getElementById(`error-${field}`);
                const inputField = document.getElementById(field);

                const texto = Array.isArray(fieldError?.messages)
                    ? fieldError.messages.join(', ')
                    : '';

                if (errorDiv) {
                    errorDiv.textContent = texto;
                }
                if (inputField) {
                    inputField.classList.add('is-invalid');
                }
            }
            return {
                status: 'error',
                message:
                    'Por favor, revise el formulario para completar correctamente los datos solicitados.',
            };
        }

        if (!response.ok) {
            return {
                status: 'error',
                message: data.message ?? `Error HTTP ${response.status}`,
            };
        }

        if (data.status === 'success') {
            const usuarioUuidHex = (data.usuario_uuid_hex || '').toString();

            if (!usuarioUuidHex || !/^[0-9a-fA-F]{32}$/.test(usuarioUuidHex)) {
                return {
                    status: 'error',
                    message: 'El backend no devolvió usuario_uuid_hex válido.',
                };
            }

            const reservaResponse = await creacioReserva(
                usuarioUuidHex,
                idReserva
            );

            if (reservaResponse?.status === 'success') {
                return {
                    status: 'success',
                    message: 'Reserva creada correctamente',
                };
            }
            return { status: 'error', message: 'No se ha creado la reserva' };
        }

        return { status: 'error', message: `Error en la solicitud` };
    } catch (error) {
        return { status: 'error', message: `Error en la solicitud ${error}` };
    }
};
