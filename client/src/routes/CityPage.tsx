import { getRouteApi, useNavigate } from "@tanstack/react-router";
import { WeatherReport } from "../components/WeatherReport";
import { useUnitSystem } from "../units/useUnitSystem";
import { WeatherQueryPage } from "./WeatherQueryPage";

const cityApi = getRouteApi("/city");
const rootApi = getRouteApi("__root__");

export function CityPage() {
  const cities = rootApi.useLoaderData();
  const { cityId, from, to } = cityApi.useSearch();
  const result = cityApi.useLoaderData();
  const navigate = useNavigate({ from: "/city" });
  const { unitSystem } = useUnitSystem();

  const options = cities.map((city) => ({
    value: city.id,
    label: `${city.name} (${city.region})`,
  }));

  return (
    <WeatherQueryPage
      selectionLabel="Város"
      options={options}
      committed={{ selection: cityId, from, to }}
      onSubmit={({ selection, from, to }) =>
        void navigate({
          search: (prev) => ({
            ...prev,
            cityId: selection,
            from,
            to,
            page: undefined,
          }),
        })
      }
      result={result}
      idleMessage="Válassz várost és időszakot, majd kattints a Lekérdezés gombra."
      renderReport={(report, isLoading, goToPage) => (
        <WeatherReport
          series={report}
          caption={`${report.city.name} (${report.city.region})`}
          unitSystem={unitSystem}
          onPageChange={goToPage}
          isLoading={isLoading}
        />
      )}
    />
  );
}
