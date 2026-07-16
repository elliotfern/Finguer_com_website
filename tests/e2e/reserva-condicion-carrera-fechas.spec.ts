import { expect, test, type Page } from '@playwright/test';

async function aceptarCookiesSiAparece(page: Page) {
    const botonCookies = page.getByRole('button', { name: 'Aceptar cookies' });
    if (await botonCookies.isVisible().catch(() => false)) {
        await botonCookies.click();
    }
}

async function seleccionarRangoFechasValido(page: Page, offset = 0) {
    await page
        .getByRole('textbox', { name: 'Clique aquí para abrir el calendario' })
        .click();

    const calendario = page.locator('.flatpickr-calendar.open');
    await expect(calendario).toBeVisible();

    const diasValidos = calendario.locator(
        '.flatpickr-day:not(.flatpickr-disabled):not(.prevMonthDay):not(.nextMonthDay):not(.dia-cierre-especial)'
    );

    const total = await diasValidos.count();
    if (total < offset + 2) {
        throw new Error(
            'No hay suficientes días válidos visibles en el mes actual para formar un rango'
        );
    }

    await diasValidos.nth(offset).click();
    await diasValidos.nth(offset + 1).click();
}

test.describe('Formulario de reserva - condición de carrera al cambiar fechas', () => {
    test('cambiar solo las fechas con red lenta no debe dejar el botón visible con horas obsoletas', async ({
        page,
    }) => {
        await page.goto('/');
        await aceptarCookiesSiAparece(page);

        // --- 1. Flujo normal hasta botón visible ---
        await page.locator('#tipo_reserva').selectOption('RESERVA_FINGUER');
        await seleccionarRangoFechasValido(page, 0);

        const horaEntradaSelect = page.locator('#horaEntrada');
        const horaSalidaSelect = page.locator('#horaSalida');

        await expect(horaEntradaSelect.locator('option')).not.toHaveCount(1);
        await expect(horaSalidaSelect.locator('option')).not.toHaveCount(1);

        await horaEntradaSelect.selectOption({ index: 1 });
        await horaSalidaSelect.selectOption({ index: 1 });

        await expect(page.locator('#pagar')).toBeVisible({ timeout: 5000 });

        // --- 2. Interceptar horas-disponibles con retraso artificial ---
        await page.route(
            '**/catalogo/get/horas-disponibles**',
            async (route) => {
                await new Promise((resolve) => setTimeout(resolve, 1500));
                await route.continue();
            }
        );

        // --- 3. Cambiar SOLO las fechas (rango distinto), sin tocar tipo_reserva ---
        await seleccionarRangoFechasValido(page, 2);

        // --- 4. Esperar a que cotizar (rápido) responda con horas antiguas ---
        await page.waitForTimeout(600); // > debounce 350ms de schedulePressupost

        // --- 5. Esperar a que la respuesta lenta de horas-disponibles llegue y vacíe los selects ---
        await expect(horaEntradaSelect).toHaveValue('', { timeout: 3000 });
        await expect(horaSalidaSelect).toHaveValue('', { timeout: 3000 });

        // --- 6. Con las horas vacías, el botón de pagar NO debería estar visible ---
        await expect(page.locator('#pagar')).toBeHidden({ timeout: 1000 });
    });
});
