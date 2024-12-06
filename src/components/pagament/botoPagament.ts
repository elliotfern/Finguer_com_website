export const botoPagament = () => {
    // Selecciona el checkbox por su ID
    const checkbox = document.getElementById('terminos_condiciones') as HTMLInputElement | null;
    // Selecciona el botón dentro del div con ID 'div_pagar'
    const botonPagar = document.querySelector('#div_pagar button') as HTMLButtonElement | null;
    // Selecciona el aviso por su ID
    const avisoTerminos = document.getElementById('aviso_terminos') as HTMLElement | null;

    if (!checkbox || !botonPagar || !avisoTerminos) {
        console.error("No se encontró alguno de los elementos en el DOM");
        return;
    }

    // Verifica si el checkbox está marcado
    if (checkbox.checked) {
        botonPagar.disabled = false; // Habilitar el botón
        avisoTerminos.style.display = 'none'; // Ocultar el aviso
    } else {
        botonPagar.disabled = true; // Deshabilitar el botón
        avisoTerminos.style.display = 'block'; // Mostrar el aviso
    }
}