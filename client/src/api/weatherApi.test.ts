import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import {
  fetchCities,
  fetchCityWeather,
  fetchRegionWeather,
} from "./weatherApi";

const jsonResponse = (body: unknown) =>
  new Response(JSON.stringify(body), {
    headers: { "Content-Type": "application/json" },
  });

const stubFetch = (impl: () => Promise<Response>) => {
  const fetchMock = vi.fn(impl);
  vi.stubGlobal("fetch", fetchMock);
  return fetchMock;
};

beforeEach(() => {
  vi.stubEnv("VITE_API_URL", "http://api.test");
});

afterEach(() => {
  vi.unstubAllGlobals();
  vi.unstubAllEnvs();
});

describe("weatherApi", () => {
  it("fetchRegionWeather URL-kódolja az ékezetes, szóközös régiót", async () => {
    const fetchMock = stubFetch(async () => jsonResponse({}));

    await fetchRegionWeather({
      region: "Észak és Alföld",
      from: "2023-01-01",
      to: "2023-01-02",
    });

    expect(fetchMock).toHaveBeenCalledWith(
      "http://api.test/weather/region?region=%C3%89szak+%C3%A9s+Alf%C3%B6ld&from=2023-01-01&to=2023-01-02",
    );
  });

  it("fetchCityWeather a cityId-t is átadja", async () => {
    const fetchMock = stubFetch(async () => jsonResponse({}));

    await fetchCityWeather({
      cityId: 7,
      from: "2023-01-01",
      to: "2023-01-02",
    });

    expect(fetchMock).toHaveBeenCalledWith(
      "http://api.test/weather/city?cityId=7&from=2023-01-01&to=2023-01-02",
    );
  });

  it("az oldalszámot átadja, ha meg van adva", async () => {
    const fetchMock = stubFetch(async () => jsonResponse({}));

    await fetchCityWeather({
      cityId: 7,
      from: "2023-01-01",
      to: "2023-12-31",
      page: 3,
    });

    expect(fetchMock).toHaveBeenCalledWith(
      "http://api.test/weather/city?cityId=7&from=2023-01-01&to=2023-12-31&page=3",
    );
  });

  it("fetchCities a /cities végpontot hívja", async () => {
    const fetchMock = stubFetch(async () => jsonResponse([]));

    await fetchCities();

    expect(fetchMock).toHaveBeenCalledWith("http://api.test/cities");
  });
});
