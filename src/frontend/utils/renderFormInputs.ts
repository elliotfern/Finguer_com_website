import { formatDatesForm } from "./dates";

export function renderFormInputs<T extends Record<string, unknown>>(data: T): void {
  for (const [key, value] of Object.entries(data)) {
    const input = document.querySelector<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>(`#${key}`);
    if (!input) continue;

    if (value === null || value === undefined) {
      input.value = '';
      continue;
    }

    // 👇 type="date" necesita YYYY-MM-DD, no formatear
    if (
      input instanceof HTMLInputElement &&
      input.type === 'date' &&
      typeof value === 'string' &&
      /^\d{4}-\d{2}-\d{2}$/.test(value)
    ) {
      input.value = value; // formato original, sin tocar
      continue;
    }

    // 👇 otros campos de texto con fecha (si los hubiera)
    if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value)) {
      input.value = formatDatesForm(value);
      continue;
    }

    input.value = String(value);
  }
}