<div class="d-flex flex-column flex-shrink-0 p-3">
    <a href="/inici/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none links-sidebar">
    <img src="<?php echo APP_WEB;?>/public/inc/img/logo3.png" alt="Logo" class="d-block mx-auto" width="150" height="80">
    </a>
<hr class="text-white">
   
    <nav class="navbar navbar-expand-lg bg-body-tertiary navbar-nav-scroll" style="display:block;background-color:#000000 !important;">
		<button class="navbar-toggler text-center" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon text-center"></span>
		</button>

    <div class="collapse navbar-collapse" id="navbarTogglerDemo01" style="background-color:#000000 !important;">

    <?php
    // Obtiene la URL actual
    $current_url = $_SERVER['REQUEST_URI'];

    // Define un array con los enlaces y sus detalles correspondientes
    $links = array(
        "/reserves" => array(
            "label" => "Llistat reserves",
            "url" => "/reserves",
            "icon" => "bi-house-door",
            "paths" => array(
                "M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4z"
            )
        ),

        "/inici" => array(
            "label" => "Estat 1: pendent",
            "url" => "/inici",
            "icon" => "bi-house-door",
            "paths" => array(
                "M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4z"
            )
        ),

        "/reserves-parking" => array(
            "label" => "Estat 2: al parking",
            "url" => "/reserves-parking",
            "icon" => "bi-coin",
            "paths" => array(
                "M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm9 1.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4a.5.5 0 0 0-.5.5M9 8a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4A.5.5 0 0 0 9 8m1 2.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 0-1h-3a.5.5 0 0 0-.5.5m-1 2C9 10.567 7.21 9 5 9c-2.086 0-3.8 1.398-3.984 3.181A1 1 0 0 0 2 13h6.96q.04-.245.04-.5M7 6a2 2 0 1 0-4 0 2 2 0 0 0 4 0",
            )
        ),

        "/reserves-completades" => array(
            "label" => "Estat 3: completades",
            "url" => "/reserves-completades",
            "icon" => "bi-people-fill",
            "paths" => array(
                "M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"
            )
        ),

        "/cercador-reserva" => array(
            "label" => "Cercador reserva",
            "url" => "/cercador-reserva",
            "icon" => "bi-list-task",
            "paths" => array(
               "M2 2.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5V3a.5.5 0 0 0-.5-.5zM3 3H2v1h1z",
               "M5 3.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M5.5 7a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1zm0 4a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1z",
                "M1.5 7a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H2a.5.5 0 0 1-.5-.5zM2 7h1v1H2zm0 3.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm1 .5H2v1h1z",
            )
        ),

        "/calendari/entrades" => array(
            "label" => "Calendari entrades",
            "url" => "/calendari/entrades",
            "icon" => "bi-list-task",
            "paths" => array(
               "M2 10h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1m9-9h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-3a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1m0 9a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zm0-10a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM2 9a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2zm7 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2zM0 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5.354.854a.5.5 0 1 0-.708-.708L3 3.793l-.646-.647a.5.5 0 1 0-.708.708l1 1a.5.5 0 0 0 .708 0z",
            )
        ),

        "/calendari/sortides" => array(
            "label" => "Calendari sortides",
            "url" => "/calendari/sortides",
            "icon" => "bi-list-task",
            "paths" => array(
               "M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-5 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z",
                "M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z",
            )
        ),

        "/cercadors" => array(
            "label" => "Buscadors",
            "url" => "/cercadors",
            "icon" => "bi-list-task",
            "paths" => array(
               "M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-5 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z",
                "M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z",
            )
        ),

        "/clients-anuals" => array(
            "label" => "Clients anuals",
            "url" => "/clients-anuals",
            "icon" => "bi-list-task",
            "paths" => array(
               "M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-5 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z",
                "M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z",
            )
        ),

    );
?>
    <ul class="nav nav-pills flex-column mb-auto">
        <?php foreach ($links as $url => $data): ?>
            <li class="nav-item">
            <a class="nav-link <?php echo (strpos($current_url, $url) === 0) ? 'active' : ''; ?>" href="<?php echo $data['url']; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="text-white bi <?php echo $data['icon']; ?> me-2" viewBox="0 0 16 16">
                    <?php foreach ($data['paths'] as $path): ?>
                        <path d="<?php echo $path; ?>"/>
                    <?php endforeach; ?>
                </svg>
                <span class="text-white links-sidebar"><?php echo $data['label']; ?></span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
    </nav>

    <hr class="text-white">

    <?php
    // Genera o obtén tu clave secreta
    $loggedInUser = ($_SESSION['user']['id']);
    ?>
      <span class="d-flex align-items-center text-decoration-none">
        <strong><div id="userDiv" class="white"> </div></strong>
    </span>     
    <a href="#" class="links-sidebar link-sortir" onclick="logout()">Log out</a>
  </div>

  <script>
        nameUser('<?php echo $loggedInUser; ?>')

       function nameUser(idUser) {
        let urlAjax =  "https://" + window.location.hostname + "/api/users/get/?type=user&id=" + idUser;
        $.ajax({
            url: urlAjax,
            type: 'GET',
            beforeSend: function (xhr) {
                // Obtener el token del localStorage
                let token = localStorage.getItem('token');

                // Incluir el token en el encabezado de autorización
                xhr.setRequestHeader('Authorization', 'Bearer ' + token);
            },
            success: function (data) {
               // Modifica el contenido de un div con el resultado de la API
               let responseData = JSON.parse(data);  // Parsea la respuesta JSON
                let welcomeMessage = responseData.name ? `Welcome, ${responseData.name}` : 'User not found';
                $('#userDiv').html(welcomeMessage);  // Muestra el mensaje en tu página

                // Alternativamente, guarda el ID de usuario en localStorage
                localStorage.setItem('user_id', responseData.id);
            },
            error: function (error) {
                console.error('Error: ' + JSON.stringify(error));
            }
        });
    }
    function deleteCookie(name, path, domain) {
    if (getCookie(name)) {
        document.cookie = name + "=" + 
          ((path) ? ";path=" + path : "") +
          ((domain) ? ";domain=" + domain : "") +
          ";expires=Thu, 01 Jan 1970 00:00:01 GMT";
    }
}

function getCookie(name) {
    const value = "; " + document.cookie;
    const parts = value.split("; " + name + "=");
    if (parts.length === 2) return parts.pop().split(";").shift();
}

function deleteAllCookies() {
    const cookies = document.cookie.split("; ");

    // Eliminar cookies en la ruta actual y todas las subrutas
    cookies.forEach(cookie => {
        const eqPos = cookie.indexOf("=");
        const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
        const pathParts = location.pathname.split('/');
        for (let i = 0; i < pathParts.length; i++) {
            const path = pathParts.slice(0, i + 1).join('/') || '/';
            deleteCookie(name, path);
            deleteCookie(name, path, window.location.hostname);
            if (window.location.hostname.includes('.')) {
                deleteCookie(name, path, '.' + window.location.hostname);
            }
        }
    });

    // Eliminar cookies en el dominio raíz
    cookies.forEach(cookie => {
        const eqPos = cookie.indexOf("=");
        const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
        deleteCookie(name, '/');
        deleteCookie(name, '/', window.location.hostname);
        if (window.location.hostname.includes('.')) {
            deleteCookie(name, '/', '.' + window.location.hostname);
        }
    });
}

function logout() {
    // Eliminar todas las cookies
    deleteAllCookies();

    // Si usas localStorage o sessionStorage, también es buena idea limpiarlos
    localStorage.clear();
    sessionStorage.clear();

    // Redirigir a la página de inicio de sesión
    window.location.href = 'https://finguer.gestionparking.net/login';
}
</script>