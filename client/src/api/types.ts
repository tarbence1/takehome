export type City = {
  id: number;
  name: string;
  region: string;
};

export type DailyWeather = {
  date: string;
  minTemp: number | null;
  maxTemp: number | null;
  avgTemp: number | null;
  precipitation: number | null;
};

/** Egy oszlop mediánja és móduszai a teljes időszakra (SI értékek). */
export type ColumnSummary = { median: number | null; modes: number[] };

export type WeatherColumnKey =
  | "minTemp"
  | "maxTemp"
  | "avgTemp"
  | "precipitation";

export type WeatherSummary = Record<WeatherColumnKey, ColumnSummary>;

export type PaginationInfo = {
  page: number;
  pageSize: number;
  totalItems: number;
  totalPages: number;
};

export type WeatherSeries = {
  from: string;
  to: string;
  /** A teljes időszak statisztikája, nem csak az aktuális oldalé. */
  summary: WeatherSummary;
  pagination: PaginationInfo;
  /** Az aktuális oldal napjai. */
  days: DailyWeather[];
};

export type CityWeatherReport = WeatherSeries & { city: City };

export type RegionWeatherReport = WeatherSeries & {
  region: string;
  cities: City[];
};
