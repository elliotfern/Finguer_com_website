export const botoPagament = () => {
  const checkbox = document.getElementById('terminos_condiciones') as HTMLInputElement | null;
  const avisoTerminos = document.getElementById('aviso_terminos') as HTMLElement | null;
  const botonPagar = document.querySelector('#div_pagar button') as HTMLButtonElement | null;

  if (!checkbox || !botonPagar || !avisoTerminos) return;

  if (checkbox.checked) {
    botonPagar.style.opacity = '1';
    botonPagar.style.pointerEvents = 'auto';
    avisoTerminos.style.display = 'none';
  } else {
    botonPagar.style.opacity = '0.5';
    botonPagar.style.pointerEvents = 'none';
    avisoTerminos.style.display = 'block';
  }
};