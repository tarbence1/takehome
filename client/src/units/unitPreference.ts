import { isUnitSystem, type UnitSystem } from "../domain/units";

const STORAGE_KEY = "unitSystem";

export function loadUnitSystem(): UnitSystem | undefined {
  try {
    const stored = window.localStorage.getItem(STORAGE_KEY);
    return isUnitSystem(stored) ? stored : undefined;
  } catch {
    return undefined;
  }
}

export function saveUnitSystem(system: UnitSystem): void {
  try {
    window.localStorage.setItem(STORAGE_KEY, system);
  } catch {
    return;
  }
}
