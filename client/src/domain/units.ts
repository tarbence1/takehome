import { roundTo2 } from "./rounding";

export type UnitSystem = "si" | "imperial";

const UNIT_SYSTEMS: readonly UnitSystem[] = ["si", "imperial"];

const MM_PER_INCH = 25.4;

export function isUnitSystem(value: unknown): value is UnitSystem {
  return UNIT_SYSTEMS.some((system) => system === value);
}

export function convertTemperature(
  celsius: number,
  system: UnitSystem,
): number {
  return roundTo2(system === "imperial" ? (celsius * 9) / 5 + 32 : celsius);
}

export function convertPrecipitation(
  millimetres: number,
  system: UnitSystem,
): number {
  return roundTo2(
    system === "imperial" ? millimetres / MM_PER_INCH : millimetres,
  );
}

export function temperatureUnit(system: UnitSystem): "°C" | "°F" {
  return system === "imperial" ? "°F" : "°C";
}

export function precipitationUnit(system: UnitSystem): "mm" | "in" {
  return system === "imperial" ? "in" : "mm";
}

export function convertModes(
  modes: readonly number[],
  convert: (value: number) => number,
): number[] {
  return [...new Set(modes.map(convert))];
}
