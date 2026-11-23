import type { WeatherSeries } from "../api/types";
import type { UnitSystem } from "../domain/units";
import { Pagination } from "./Pagination";
import { WeatherTable } from "./WeatherTable";

type WeatherReportProps = {
  series: WeatherSeries;
  caption: string;
  unitSystem: UnitSystem;
  onPageChange: (page: number) => void;
  isLoading: boolean;
};

export function WeatherReport({
  series,
  caption,
  unitSystem,
  onPageChange,
  isLoading,
}: WeatherReportProps) {
  const { days, summary, pagination } = series;

  return (
    <div className="space-y-4">
      <WeatherTable
        caption={caption}
        days={days}
        summary={summary}
        unitSystem={unitSystem}
      />
      <Pagination
        page={pagination.page}
        totalPages={pagination.totalPages}
        onPageChange={onPageChange}
        disabled={isLoading}
      />
    </div>
  );
}
