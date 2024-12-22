export const avisEspecialTancamentParking = (show: boolean) => {
  const avisoDiv = document.getElementById('avis_especial') as HTMLElement | null;

  if (show) {
    if (avisoDiv) {
      avisoDiv.innerHTML = `<h4><strong>Aviso Navidad</strong></h4>
          Nuestros horarios de apertura serán los siguientes:
          <ul>
          <li><strong>24 de diciembre:</strong> abierto hasta las 18:00h.</li>
          <li><strong>25 de diciembre:</strong> párking cerrado.</li>
          <li><strong>26 de diciembre:</strong> abierto a partir de las 12:00h.</li>
          <li><strong>31 de diciembre:</strong> abierto hasta las 18:00h.</li>
          <li><strong>1 de enero:</strong> abierto a partir de las 12:00h.</li>
          </ul>`;
      avisoDiv.style.display = 'block';
      setTimeout(() => {
        avisoDiv.style.opacity = '1'; // Animamos la opacidad
        avisoDiv.style.transform = 'translateY(0)'; // O un pequeño efecto de desplazamiento
      }, 10);
    }
  }
};
