import { Role } from "../../../types/Role";

export type Me = Readonly<{
  uuid: string;
  role: Role;
  name: string;
}>;

let me: Me | null = null;

export function setMe(value: Me | null): void {
  me = value;
}

export function getMe(): Me | null {
  return me;
}

export function getRole(): Role | null {
  return me?.role ?? null;
}

export function isAdmin(): boolean {
  return me?.role === "admin";
}
