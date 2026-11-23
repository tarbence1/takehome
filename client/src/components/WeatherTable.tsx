import { useEffect, useRef } from "react";
import type {
  DailyWeather,
  WeatherColumnKey,
  WeatherSummary,
} from "../api/types";
import { formatDate, formatMeasurement, formatModes } from "../domain/format";
import {
  convertModes,
  convertPrecipitation,
  convertTemperature,
  precipitationUnit,
  temperatureUnit,
  type UnitSystem,
} from "../domain/units";
import { StatusMessage } from "./StatusMessage";

type Column = {
  key: WeatherColumnKey;
  label: string;
  unit: (system: UnitSystem) => string;
  convert: (value: number, system: UnitSystem) => number;
};

const TEMPERATURE = { unit: temperatureUnit, convert: convertTemperature };
const PRECIPITATION = {
  unit: precipitationUnit,
  convert: convertPrecipitation,
};

const COLUMNS: readonly Column[] = [
  { key: "minTemp", label: "Minimum", ...TEMPERATURE },
  { key: "maxTemp", label: "Maximum", ...TEMPERATURE },
  { key: "avgTemp", label: "Átlag", ...TEMPERATURE },
  { key: "precipitation", label: "Csapadék", ...PRECIPITATION },
];

/** Nyers SI érték → megjelenített szöveg ("–" ha hiányzik). */
function formatCell(
  column: Column,
  value: number | null,
  system: UnitSystem,
): string {
  return formatMeasurement(
    value === null ? null : column.convert(value, system),
    column.unit(system),
  );
}

const CELL_CLASS = "px-4 py-2 tabular-nums whitespace-nowrap text-right";
const HEAD_CELL_CLASS = "bg-slate-100 px-4 py-2 font-semibold";
const LABEL_CELL_CLASS = "px-4 py-2 whitespace-nowrap text-left";

type WeatherTableProps = {
  days: readonly DailyWeather[];
  summary: WeatherSummary;
  unitSystem: UnitSystem;
  caption: string;
};

export function WeatherTable({
  days,
  summary,
  unitSystem,
  caption,
}: WeatherTableProps) {
  const scrollRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    scrollRef.current?.scrollTo({ top: 0 });
  }, [days]);

  if (days.length === 0) {
    return (
      <StatusMessage kind="info">
        Nincs adat a kiválasztott időszakra
      </StatusMessage>
    );
  }

  return (
    <div
      ref={scrollRef}
      role="region"
      aria-label={caption}
      tabIndex={0}
      className="max-h-[70vh] overflow-auto rounded-xl border border-slate-200
       bg-white shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500"
    >
      <table className="w-full border-collapse text-sm">
        <caption className="px-4 py-3 text-left text-lg font-semibold">
          {caption}
        </caption>
        <thead className="sticky top-0 z-10 shadow-[0_1px_0_0_var(--color-slate-200)]">
          <tr>
            <th scope="col" className={`${HEAD_CELL_CLASS} text-left`}>
              Dátum
            </th>
            {COLUMNS.map((column) => (
              <th
                key={column.key}
                scope="col"
                className={`${HEAD_CELL_CLASS} text-right`}
              >
                {column.label} ({column.unit(unitSystem)})
              </th>
            ))}
          </tr>
        </thead>
        <tbody className="divide-y divide-slate-100">
          {days.map((day) => (
            <tr key={day.date} className="odd:bg-white even:bg-slate-50">
              <th scope="row" className={`${LABEL_CELL_CLASS} font-normal`}>
                {formatDate(day.date)}
              </th>
              {COLUMNS.map((column) => (
                <td key={column.key} className={CELL_CLASS}>
                  {formatCell(column, day[column.key], unitSystem)}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
        <tfoot className="sticky bottom-0 z-10 bg-slate-100 shadow-[0_-1px_0_0_var(--color-slate-200)]">
          <tr>
            <th scope="row" className={`${LABEL_CELL_CLASS} font-semibold`}>
              Medián (teljes időszak)
            </th>
            {COLUMNS.map((column) => (
              <td key={column.key} className={`${CELL_CLASS} font-semibold`}>
                {formatCell(column, summary[column.key].median, unitSystem)}
              </td>
            ))}
          </tr>
          <tr>
            <th scope="row" className={`${LABEL_CELL_CLASS} font-semibold`}>
              Módusz (teljes időszak)
            </th>
            {COLUMNS.map((column) => {
              const modes = formatModes(
                convertModes(summary[column.key].modes, (value) =>
                  column.convert(value, unitSystem),
                ),
                (value) => formatMeasurement(value, column.unit(unitSystem)),
              );
              return (
                <td
                  key={column.key}
                  title={modes.title}
                  className={`${CELL_CLASS} font-semibold`}
                >
                  {modes.label}
                </td>
              );
            })}
          </tr>
        </tfoot>
      </table>
    </div>
  );
}
