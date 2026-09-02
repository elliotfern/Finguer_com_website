function setCookie(name: string, value: boolean, days: number): void {
    const expires = new Date();

    expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);

    document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
}

function getCookie(name: string): string | null {
    const cookieArr = document.cookie.split(';');

    for (const cookie of cookieArr) {
        const cookiePair = cookie.split('=');

        if (name === cookiePair[0].trim()) {
            return cookiePair[1] ? decodeURIComponent(cookiePair[1]) : null;
        }
    }

    return null;
}

function loadGoogleAnalytics(): void {
    const script = document.createElement('script');

    script.async = true;
    script.src = 'https://www.googletagmanager.com/gtag/js?id=G-NH541ZSG2V';

    document.head.appendChild(script);

    window.dataLayer = window.dataLayer || [];

    function gtag(...args: unknown[]): void {
        window.dataLayer.push(args);
    }

    gtag('js', new Date());
    gtag('config', 'G-NH541ZSG2V');
}

document.addEventListener('DOMContentLoaded', () => {
    const cookieBanner = document.getElementById('cookie-banner');
    const acceptCookiesBtn = document.getElementById('accept-cookies');
    const rejectCookiesBtn = document.getElementById('reject-cookies');

    if (
        !(cookieBanner instanceof HTMLElement) ||
        !(acceptCookiesBtn instanceof HTMLElement) ||
        !(rejectCookiesBtn instanceof HTMLElement)
    ) {
        return;
    }

    acceptCookiesBtn.addEventListener('click', () => {
        setCookie('cookiesAccepted', true, 30);
        cookieBanner.style.display = 'none';
        loadGoogleAnalytics();
    });

    rejectCookiesBtn.addEventListener('click', () => {
        setCookie('cookiesAccepted', false, 30);
        cookieBanner.style.display = 'none';
    });

    const cookiesAccepted = getCookie('cookiesAccepted');

    if (cookiesAccepted === 'true') {
        loadGoogleAnalytics();
        cookieBanner.style.display = 'none';
    } else if (cookiesAccepted === 'false') {
        cookieBanner.style.display = 'none';
    } else {
        cookieBanner.style.display = 'block';
    }
});
