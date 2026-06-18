// src/utils/auxiliarSelect.ts
import Choices from 'choices.js';
import 'choices.js/public/assets/styles/choices.min.css';

type Item = { id: number | string; [key: string]: unknown };

const choicesRegistry = new Map<string, Choices>();
const ZERO_UUID = /^0{8}-0{4}-0{4}-0{4}-0{12}$/i;

const isEmptySel = (v: unknown): boolean => v === null || v === undefined || v === '' || v === 0 || v === '0' || (typeof v === 'string' && ZERO_UUID.test(v));

function pluckItems(json: any): Item[] {
  if (Array.isArray(json?.data?.items)) return json.data.items;
  if (Array.isArray(json?.items)) return json.items;
  if (Array.isArray(json?.data)) return json.data;
  if (Array.isArray(json)) return json;
  return [];
}

/**
 * Rellena un <select> con datos remotos y preselecciona valores.
 * @param selected valor/es seleccionados (string|number|array) o null/undefined si no hay
 * @param api      nombre de recurso auxiliar (se concatena a /auxiliars/get/{api})
 * @param elementId id del <select>
 * @param valorText campo del item que se mostrará como etiqueta (p.ej. 'pais_ca')
 * @param fallbackValue valor a usar si selected es vacío
 * @param config   opciones extra para Choices
 */
export async function auxiliarSelect(selected: number | string | Array<number | string> | null | undefined, api: string, elementId: string, valorText: string, fallbackValue?: number | string, config?: any,  disabled = false): Promise<Choices | void> {
  const urlAjax = `${api}`;

  try {
    const response = await fetch(urlAjax, { method: 'GET', credentials: 'include' });
    if (!response.ok) throw new Error(`Error HTTP ${response.status}`);

    const jsonResponse = await response.json();
    const data: Item[] = pluckItems(jsonResponse);

    const selectElement = document.getElementById(elementId) as HTMLSelectElement | null;
    if (!selectElement) return;

    // Destruir instancia anterior de Choices si existía
    const prev = choicesRegistry.get(elementId);
    if (prev) {
      prev.destroy();
      choicesRegistry.delete(elementId);
    }

    // Limpiar y añadir placeholder
    selectElement.innerHTML = '';
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.text = 'Selecciona una opció:';
    selectElement.appendChild(placeholder);

    // Normalizar selección inicial -> array<string> sin vacíos/UUID cero
    let selectedValues: string[] = [];
    if (Array.isArray(selected)) {
      selectedValues = selected.map(String).filter((v) => !isEmptySel(v));
    } else if (!isEmptySel(selected)) {
      selectedValues = [String(selected)];
    } else if (!isEmptySel(fallbackValue)) {
      selectedValues = [String(fallbackValue!)];
    }

    // Instanciar Choices
    const choices = new Choices(selectElement, {
      searchEnabled: true,
      allowHTML: false,
      shouldSort: false, // conserva placeholder primero
      placeholder: true,
      placeholderValue: 'Selecciona una opció:',
      removeItemButton: !!selectElement.multiple, // botón de quitar solo en múltiple
      itemSelectText: '',
      noResultsText: 'Sense resultats',
      ...config,
    });

    if (disabled) {
  choices.disable();
  selectElement.setAttribute('disabled', 'true');

  const container = choices.containerOuter.element;
  const inner = container.querySelector('.choices__inner') as HTMLElement;

  if (inner) {
    // 🔥 FORZAR estilo legible
    inner.style.backgroundColor = '#fff';
    inner.style.color = '#212529';
    inner.style.opacity = '1';
    inner.style.filter = 'none';
    inner.style.cursor = 'default';
    inner.style.border = '1px solid #dee2e6';
    inner.style.boxShadow = 'none';
  }

  // quitar overlay gris de Choices
  container.classList.remove('is-disabled');
}

    // Construir opciones
    const options = data.map((item) => {
      const raw = item[valorText];
      const label = typeof raw === 'string' ? raw : String(raw ?? item['nom'] ?? item['name'] ?? item['label'] ?? item['title'] ?? item.id ?? '');
      return { value: String(item.id), label };
    });

    // Inyectar opciones sin resetear selección
    choices.setChoices(options, 'value', 'label', false);

    // Preselección segura (sin querySelector '#...')
    if (selectedValues.length > 0) {
      choices.setChoiceByValue(selectedValues);
    } else {
      selectElement.value = '';
      choices.removeActiveItems();
    }

    // Propagar cambios al DOM/otros listeners
    selectElement.addEventListener('change', () => {
      // nada extra; este evento permite reaccionar fuera
    });

    choicesRegistry.set(elementId, choices);
    return choices;
  } catch (error) {
    console.error('Error al cargar las opciones:', error);
  }
}
