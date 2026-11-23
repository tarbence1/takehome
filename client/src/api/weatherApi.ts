import { getJson } from "./httpClient";
import type { City, CityWeatherReport, RegionWeatherReport } from "./types";

type PagedRangeQuery = { from: string; to: string; page?: number };

/** Az összes város, név szerint rendezve. */
export function fetchCities(): Promise<City[]> {
  return getJson<City[]>("/cities");
}

/** Egy város napi adatai a megadott tartományra, oldalanként. */
export function fetchCityWeather(
  query: { cityId: number } & PagedRangeQuery,
): Promise<CityWeatherReport> {
  return getJson<CityWeatherReport>("/weather/city", query);
}

/** Egy régió városonkénti átlagolt napi adatai a megadott tartományra, oldalanként. */
export function fetchRegionWeather(
  query: { region: string } & PagedRangeQuery,
): Promise<RegionWeatherReport> {
  return getJson<RegionWeatherReport>("/weather/region", query);
}
