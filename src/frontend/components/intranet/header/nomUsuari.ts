import { apiUrl } from "../../../config/globals";

(async () => {
  const me = await nomUsuari();
  console.log("ROLE:", me?.role ?? "NO AUTH");
})();

type Role = "admin" | "trabajador" | "cliente";

type MeSuccess = Readonly<{
  status: "success";
  data: {
    uuid: string;
    role: Role;
    name: string;
  };
}>;

type MeError = Readonly<{
  status: "error";
  message?: string;
  details?: string;
}>;

type MeResponse = MeSuccess | MeError;

function isRecord(v: unknown): v is Record<string, unknown> {
  return typeof v === "object" && v !== null;
}

function isRole(v: unknown): v is Role {
  return v === "admin" || v === "trabajador" || v === "cliente";
}

function parseMeResponse(json: unknown): MeResponse {
  if (!isRecord(json)) return { status: "error", message: "Bad JSON" };

  const status = json["status"];
  if (status === "success") {
    const data = json["data"];
    if (!isRecord(data)) return { status: "error", message: "Missing data" };

    const uuid = data["uuid"];
    const role = data["role"];
    const name = data["name"];

    if (typeof uuid !== "string") return { status: "error", message: "Bad uuid" };
    if (!isRole(role)) return { status: "error", message: "Bad role" };
    if (typeof name !== "string") return { status: "error", message: "Bad name" };

    return { status: "success", data: { uuid, role, name } };
  }

  // status error (o desconocido)
  const message = json["message"];
  const details = json["details"];
  return {
    status: "error",
    message: typeof message === "string" ? message : "Unknown error",
    details: typeof details === "string" ? details : undefined,
  };
}

export async function nomUsuari(): Promise<{ role: Role } | null> {
  const urlAjax = `${apiUrl}/intranet/users/get/?type=user`;

  try {
    const response = await fetch(urlAjax, {
      method: "GET",
      credentials: "include",
      headers: { Accept: "application/json" },
    });

    // Tu endpoint usa 403 cuando no hay auth
    if (response.status === 401 || response.status === 403) {
      return null;
    }

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const json: unknown = await response.json();
    const parsed = parseMeResponse(json);

    if (parsed.status !== "success") {
      return null;
    }

    const { name, role } = parsed.data;

    const welcomeMessage = name ? `Benvingut, ${name}` : "Usuari no trobat";
    const userDiv = document.getElementById("userDiv");
    if (userDiv) userDiv.textContent = welcomeMessage;

    return { role };
  } catch (error) {
    console.error("Error:", error);
    return null;
  }
}
