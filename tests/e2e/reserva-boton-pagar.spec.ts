import { expect, test, type Page } from '@playwright/test';

async function aceptarCookiesSiAparece(page: Page) {
    const botonCookies = page.getByRole('button', { name: 'Aceptar cookies' });
    if (await botonCookies.isVisible().catch(() => false)) {
        await botonCookies.click();
    }
}

async function seleccionarRangoFechasValido(page: Page) {
    await page
        .getByRole('textbox', { name: 'Clique aquí para abrir el calendario' })
        .click();

    const calendario = page.locator('.flatpickr-calendar.open');
    await expect(calendario).toBeVisible();

    const diasValidos = calendario.locator(
        '.flatpickr-day:not(.flatpickr-disabled):not(.prevMonthDay):not(.nextMonthDay):not(.dia-cierre-especial)'
    );

    const total = await diasValidos.count();
    if (total < 2) {
        throw new Error(
            'No hay suficientes días válidos visibles en el mes actual para formar un rango'
        );
    }

    await diasValidos.nth(0).click();
    await diasValidos.nth(1).click();
}

test.describe('Formulario de reserva - botón de pagar', () => {
    test('flujo correcto: seleccionar tipo, fechas y horas muestra el botón de pagar', async ({
        page,
    }) => {
        await page.goto('/');
        await aceptarCookiesSiAparece(page);

        await expect(page.locator('#pagar')).toBeHidden();

        await page.locator('#tipo_reserva').selectOption('RESERVA_FINGUER');
        await seleccionarRangoFechasValido(page);

        const horaEntradaSelect = page.locator('#horaEntrada');
        const horaSalidaSelect = page.locator('#horaSalida');

        await expect(horaEntradaSelect.locator('option')).not.toHaveCount(1);
        await expect(horaSalidaSelect.locator('option')).not.toHaveCount(1);

        await horaEntradaSelect.selectOption({ index: 1 });
        await horaSalidaSelect.selectOption({ index: 1 });

        await expect(page.locator('#pagar')).toBeVisible({ timeout: 5000 });
    });
});
