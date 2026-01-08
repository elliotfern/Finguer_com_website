//import { HTMLTrixEditorElement } from '../../types/types';

export function resetForm(formId: string) {
  const formulario = document.getElementById(formId) as HTMLFormElement;
  const inputs = formulario.querySelectorAll('input, textarea, select, trix-editor');

  inputs.forEach((input) => {
    if (input instanceof HTMLInputElement || input instanceof HTMLTextAreaElement) {
      input.value = ''; // Limpiar el valor del campo
    }
    if (input instanceof HTMLSelectElement) {
      input.selectedIndex = 0; // Limpiar el select (poner el primer valor por defecto)
    }
    /*
    if (input instanceof HTMLElement && input.tagName === 'TRIX-EDITOR') {
      const trixEditor = input as HTMLTrixEditorElement;
      trixEditor.editor.loadHTML(''); // Limpiar el contenido del editor Trix
    } */
  });
}
