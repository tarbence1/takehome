import { getRouteApi, useNavigate } from "@tanstack/react-router";
import type { UnitSystem } from "../domain/units";
import { loadUnitSystem, saveUnitSystem } from "./unitPreference";

const rootApi = getRouteApi("__root__");

const DEFAULT_UNIT_SYSTEM: UnitSystem = "si";

export function useUnitSystem(): {
  unitSystem: UnitSystem;
  setUnitSystem: (system: UnitSystem) => void;
} {
  const { units } = rootApi.useSearch();
  const navigate = useNavigate();

  const setUnitSystem = (system: UnitSystem) => {
    saveUnitSystem(system);
    void navigate({
      to: ".",
      search: (prev: Record<string, unknown>) => ({ ...prev, units: system }),
      replace: true,
    });
  };

  return {
    unitSystem: units ?? loadUnitSystem() ?? DEFAULT_UNIT_SYSTEM,
    setUnitSystem,
  };
}
