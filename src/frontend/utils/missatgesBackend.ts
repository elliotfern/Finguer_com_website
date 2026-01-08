export function missatgesBackend({ tipus, missatge, contenidor, text, altreContenidor }: { tipus: 'success' | 'error'; missatge: string; contenidor: HTMLElement; text: HTMLElement; altreContenidor?: HTMLElement }): void {
  if (altreContenidor) {
    altreContenidor.style.display = 'none';
    altreContenidor.classList.remove('alert-success', 'alert-danger');
  }

  const heading = tipus === 'success' ? '<h4 class="alert-heading"><strong>Transmissió de dades correcta!</strong></h4>' : '<h4 class="alert-heading"><strong>Error en les dades!</strong></h4>';

  text.innerHTML = `${heading}${missatge}`;
  contenidor.style.display = 'block';

  contenidor.classList.remove('alert-success', 'alert-danger');
  contenidor.classList.add(tipus === 'success' ? 'alert-success' : 'alert-danger');
  contenidor.scrollIntoView({ behavior: 'smooth', block: 'center' });

  setTimeout(() => {
    contenidor.style.display = 'none';
    contenidor.classList.remove('alert-success', 'alert-danger');
  }, 15000);
}