export function missatgesBackend({
  tipus,
  missatge,
  contenidor,
  text,
  altreContenidor
}: {
  tipus: 'success' | 'error';
  missatge: string;
  contenidor: HTMLElement;
  text: HTMLElement;
  altreContenidor?: HTMLElement;
}): void {

  if (altreContenidor) {
    altreContenidor.classList.add('d-none');
    altreContenidor.classList.remove('alert-success', 'alert-danger');
  }

  const heading =
    tipus === 'success'
      ? '<h4 class="alert-heading"><strong>Transmissió de dades correcta!</strong></h4>'
      : '<h4 class="alert-heading"><strong>Error en les dades!</strong></h4>';

  text.innerHTML = `${heading}${missatge}`;

  // 🔥 IMPORTANTE: quitar d-none
  contenidor.classList.remove('d-none');

  contenidor.classList.remove('alert-success', 'alert-danger');
  contenidor.classList.add(tipus === 'success' ? 'alert-success' : 'alert-danger');

  contenidor.scrollIntoView({ behavior: 'smooth', block: 'center' });

  setTimeout(() => {
    contenidor.classList.add('d-none');
    contenidor.classList.remove('alert-success', 'alert-danger');
  }, 15000);
}