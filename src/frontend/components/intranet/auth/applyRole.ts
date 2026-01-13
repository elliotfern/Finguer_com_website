import { Role } from "../../../types/Role";

type DenyMode = "hide" | "disable";

function getDenyMode(el: HTMLElement): DenyMode {
  const v = el.dataset.denyMode;
  return v === "disable" ? "disable" : "hide";
}

function deny(el: HTMLElement, mode: DenyMode): void {
  if (mode === "disable") {
    // Si es botón / input, deshabilita. Si no, lo “apagas”.
    if (el instanceof HTMLButtonElement || el instanceof HTMLInputElement || el instanceof HTMLSelectElement || el instanceof HTMLTextAreaElement) {
      el.disabled = true;
    } else {
      el.setAttribute("aria-disabled", "true");
      el.classList.add("is-disabled");
    }
  } else {
    el.hidden = true; // mejor que display:none: mantiene semántica simple
  }
}

export function applyRoleToDom(role: Role): void {
  // 1) Elementos con roles requeridos
  const nodes = document.querySelectorAll<HTMLElement>("[data-requires-role]");
  nodes.forEach((el) => {
    const required = el.dataset.requiresRole; // string | undefined
    if (!required) return;

    // soporta: data-requires-role="admin" o "admin,trabajador"
    const requiredRoles = required
      .split(",")
      .map((s) => s.trim())
      .filter((s) => s.length > 0) as Role[]; // (ok: validamos abajo)

    // Si el atributo tiene algo raro, por seguridad lo denegamos
    const isValid = requiredRoles.every((r) => r === "admin" || r === "trabajador" || r === "cliente" || r === "cliente_anual");
    if (!isValid) {
      deny(el, getDenyMode(el));
      return;
    }

    if (!requiredRoles.includes(role)) {
      deny(el, getDenyMode(el));
    }
  });

  // 2) Elementos que se ocultan específicamente para un rol
  const hideFor = document.querySelectorAll<HTMLElement>("[data-hide-for]");
  hideFor.forEach((el) => {
    const v = el.dataset.hideFor;
    if (!v) return;

    const roles = v.split(",").map((s) => s.trim());
    if (roles.includes(role)) {
      deny(el, getDenyMode(el));
    }
  });
}
