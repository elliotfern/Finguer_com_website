document.addEventListener("DOMContentLoaded", function() {
    const cookieBanner = document.getElementById("cookie-banner");
    const acceptCookiesBtn = document.getElementById("accept-cookies");
    const rejectCookiesBtn = document.getElementById("reject-cookies");

    acceptCookiesBtn.addEventListener("click", function() {
        setCookie("cookiesAccepted", true, 30);
        cookieBanner.style.display = "none";
        loadGoogleAnalytics();
    });

    rejectCookiesBtn.addEventListener("click", function() {
        setCookie("cookiesAccepted", false, 30);
        cookieBanner.style.display = "none";
    });

    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
    }

    function getCookie(name) {
        const cookieArr = document.cookie.split(";");
        for (let i = 0; i < cookieArr.length; i++) {
            const cookiePair = cookieArr[i].split("=");
            if (name === cookiePair[0].trim()) {
                return decodeURIComponent(cookiePair[1]);
            }
        }
        return null;
    }

    const cookiesAccepted = getCookie("cookiesAccepted");
    if (cookiesAccepted === "true") {
        loadGoogleAnalytics();
        cookieBanner.style.display = "none"; // Oculta el banner si ya se aceptaron las cookies
    } else if (cookiesAccepted === "false") {
        cookieBanner.style.display = "none"; // Oculta el banner si ya se rechazaron las cookies
    } else {
        // Aún no se ha tomado ninguna acción con respecto a las cookies
        cookieBanner.style.display = "block";
    }

    function loadGoogleAnalytics() {
        const script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=G-NH541ZSG2V';
        document.head.appendChild(script);

        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-NH541ZSG2V');
    }
});