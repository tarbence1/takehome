import type { UnitSystem } from "../domain/units";
import { useUnitSystem } from "./useUnitSystem";

const OPTIONS: { system: UnitSystem; label: string }[] = [
  { system: "si", label: "°C, mm" },
  { system: "imperial", label: "°F, in" },
];

export function UnitToggle() {
  const { unitSystem, setUnitSystem } = useUnitSystem();

  return (
    <div
      role="group"
      aria-label="Mértékegység"
      className="inline-flex rounded-lg bg-slate-100 p-1"
    >
      {OPTIONS.map(({ system, label }) => {
        const active = system === unitSystem;
        return (
          <button
            key={system}
            type="button"
            aria-pressed={active}
            onClick={() => setUnitSystem(system)}
            className={`cursor-pointer rounded-md px-3 py-1 text-sm font-medium focus:outline-none
             focus-visible:ring-2 focus-visible:ring-sky-500 ${
              active
                ? "bg-white text-sky-700 shadow-sm"
                : "text-slate-600 hover:text-slate-900"
            }`}
          >
            {label}
          </button>
        );
      })}
    </div>
  );
}
