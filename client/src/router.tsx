import {
  createHashHistory,
  createRootRoute,
  createRoute,
  createRouter,
  redirect,
} from "@tanstack/react-router";
import { Layout } from "./components/Layout";
import { RootPending } from "./components/RootPending";
import { RootError } from "./components/RootError";
import {
  fetchCities,
  fetchCityWeather,
  fetchRegionWeather,
} from "./api/weatherApi";
import { isUnitSystem, type UnitSystem } from "./domain/units";
import { CityPage } from "./routes/CityPage";
import { loadWhenComplete } from "./routes/loadResult";
import { RegionPage } from "./routes/RegionPage";
import {
  parseCityId,
  parseDateRange,
  parsePage,
  parseRegion,
  type DateRangeSearch,
} from "./routes/searchParams";

export const rootRoute = createRootRoute({
  validateSearch: (search: Record<string, unknown>): { units?: UnitSystem } =>
    isUnitSystem(search.units) ? { units: search.units } : {},
  loader: () => fetchCities(),
  staleTime: Infinity,
  component: Layout,
  pendingComponent: RootPending,
  errorComponent: RootError,
});

const indexRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/",
  beforeLoad: () => {
    throw redirect({ to: "/city", search: (prev) => prev, replace: true });
  },
});

const cityRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/city",
  validateSearch: (
    search: Record<string, unknown>,
  ): DateRangeSearch & { cityId?: number; page?: number } => {
    const cityId = parseCityId(search.cityId);
    const page = parsePage(search.page);
    return {
      ...(cityId !== undefined && { cityId }),
      ...parseDateRange(search),
      ...(page !== undefined && { page }),
    };
  },
  loaderDeps: ({ search }) => ({
    cityId: search.cityId,
    from: search.from,
    to: search.to,
    page: search.page,
  }),
  loader: ({ deps: { cityId, from, to, page } }) =>
    loadWhenComplete(
      cityId !== undefined && from !== undefined && to !== undefined
        ? () => fetchCityWeather({ cityId, from, to, page })
        : null,
    ),
  component: CityPage,
});

const regionRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/region",
  validateSearch: (
    search: Record<string, unknown>,
  ): DateRangeSearch & { region?: string; page?: number } => {
    const region = parseRegion(search.region);
    const page = parsePage(search.page);
    return {
      ...(region !== undefined && { region }),
      ...parseDateRange(search),
      ...(page !== undefined && { page }),
    };
  },
  loaderDeps: ({ search }) => ({
    region: search.region,
    from: search.from,
    to: search.to,
    page: search.page,
  }),
  loader: ({ deps: { region, from, to, page } }) =>
    loadWhenComplete(
      region !== undefined && from !== undefined && to !== undefined
        ? () => fetchRegionWeather({ region, from, to, page })
        : null,
    ),
  component: RegionPage,
});

const routeTree = rootRoute.addChildren([indexRoute, cityRoute, regionRoute]);

export const router = createRouter({
  routeTree,
  history: createHashHistory(),
});

declare module "@tanstack/react-router" {
  interface Register {
    router: typeof router;
  }
}
