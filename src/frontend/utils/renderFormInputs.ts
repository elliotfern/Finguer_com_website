import { formatDatesForm } from "./dates";

export function renderFormInputs<T extends Record<string, unknown>>(data: T): void {
  for (const [key, value] of Object.entries(data)) {
    const input = document.querySelector<HTMLInputElement | HTMLTextAreaElement>(`#${key}`);
    if (!input) continue;

    if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value)) {
      input.value = formatDatesForm(value);
    } else if (value === null || value === undefined) {
      input.value = '';
    } else {
      input.value = String(value);
    }
  }
}
