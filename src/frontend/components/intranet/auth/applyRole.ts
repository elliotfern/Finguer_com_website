import { Role } from "../../../types/Role";

function deny(el: HTMLElement, mode: "hide" | "disable"): void {
  if (mode === "disable") {
    if (
      el instanceof HTMLButtonElement ||
      el instanceof HTMLInputElement ||
      el instanceof HTMLSelectElement ||
      el instanceof HTMLTextAreaElement
    ) {
      el.disabled = true;
    } else {
      el.setAttribute("aria-disabled", "true");
      el.classList.add("is-disabled");
    }
  } else {
    el.hidden = true;
  }
}

function getMode(el: HTMLElement): "hide" | "disable" {
  return el.dataset.denyMode === "disable" ? "disable" : "hide";
}

function isRole(v: string): v is Role {
  return v === "admin" || v === "trabajador" || v === "cliente" || v === "cliente_anual";
}

export function applyRoleToDom(role: Role): void {
  // data-requires-role="admin" o "admin,trabajador"
  document.querySelectorAll<HTMLElement>("[data-requires-role]").forEach((el) => {
    const required = el.dataset.requiresRole;
    if (!required) return;

    const roles = required.split(",").map((s) => s.trim()).filter(Boolean);

    // si alguien puso un rol inválido en el HTML, por seguridad ocultamos
    if (!roles.every(isRole)) {
      deny(el, getMode(el));
      return;
    }

    if (!roles.includes(role)) {
      deny(el, getMode(el));
    }
  });

  // data-hide-for="trabajador" o "trabajador,cliente"
  document.querySelectorAll<HTMLElement>("[data-hide-for]").forEach((el) => {
    const hideFor = el.dataset.hideFor;
    if (!hideFor) return;

    const roles = hideFor.split(",").map((s) => s.trim()).filter(Boolean);
    if (roles.some((r) => isRole(r) && r === role)) {
      deny(el, getMode(el));
    }
  });
}
