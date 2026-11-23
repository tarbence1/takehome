import { getRouteApi, useNavigate } from "@tanstack/react-router";
import { WeatherReport } from "../components/WeatherReport";
import { useUnitSystem } from "../units/useUnitSystem";
import { WeatherQueryPage } from "./WeatherQueryPage";

const regionApi = getRouteApi("/region");
const rootApi = getRouteApi("__root__");

export function RegionPage() {
  const cities = rootApi.useLoaderData();
  const { region, from, to } = regionApi.useSearch();
  const result = regionApi.useLoaderData();
  const navigate = useNavigate({ from: "/region" });
  const { unitSystem } = useUnitSystem();

  const regions = [...new Set(cities.map((city) => city.region))].sort((a, b) =>
    a.localeCompare(b, "hu"),
  );
  const options = regions.map((name) => ({ value: name, label: name }));

  return (
    <WeatherQueryPage
      selectionLabel="Régió"
      options={options}
      committed={{ selection: region, from, to }}
      onSubmit={({ selection, from, to }) =>
        void navigate({
          search: (prev) => ({
            ...prev,
            region: selection,
            from,
            to,
            page: undefined,
          }),
        })
      }
      result={result}
      idleMessage="Válassz régiót és időszakot, majd kattints a Lekérdezés gombra."
      renderReport={(report, isLoading, goToPage) => (
        <WeatherReport
          series={report}
          caption={`${report.region}: ${report.cities.map((city) => city.name).join(", ")}`}
          unitSystem={unitSystem}
          onPageChange={goToPage}
          isLoading={isLoading}
        />
      )}
    />
  );
}
