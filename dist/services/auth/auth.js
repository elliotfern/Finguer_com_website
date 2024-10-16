var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
import { devDirectory } from '../../config.js';
export function login(userName, password) {
    return __awaiter(this, void 0, void 0, function* () {
        const urlAjax = `${devDirectory}/api/auth/login`;
        try {
            const response = yield fetch(urlAjax, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ userName, password })
            });
            const data = yield response.json();
            if (data.status === "success" && data.token) {
                localStorage.setItem('token', data.token);
                localStorage.setItem('user_id', data.idUser);
                // Mostrar mensaje de éxito
                const loginMessageOk = document.querySelector("#loginMessageOk");
                const loginMessageErr = document.querySelector("#loginMessageErr");
                if (loginMessageOk && loginMessageErr) {
                    loginMessageOk.style.display = "block";
                    loginMessageErr.style.display = "none";
                }
                // Redirigir después de un pequeño retraso
                setTimeout(() => {
                    window.location.href = `${devDirectory}/admin`;
                }, 1300);
            }
            else {
                // Mostrar mensaje de error
                const loginMessageOk = document.querySelector("#loginMessageOk");
                const loginMessageErr = document.querySelector("#loginMessageErr");
                if (loginMessageOk && loginMessageErr) {
                    loginMessageOk.style.display = "none";
                    loginMessageErr.style.display = "block";
                }
            }
        }
        catch (error) {
            console.error("Error al iniciar sesión:", error);
        }
    });
}
