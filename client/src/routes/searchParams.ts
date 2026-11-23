import { isIsoDate } from "../domain/dateRange";

export type DateRangeSearch = { from?: string; to?: string };

export function parseDateRange(
  search: Record<string, unknown>,
): DateRangeSearch {
  return {
    ...(isIsoDate(search.from) && { from: search.from }),
    ...(isIsoDate(search.to) && { to: search.to }),
  };
}

function parsePositiveInt(value: unknown): number | undefined {
  const number = typeof value === "string" ? Number(value) : value;
  return typeof number === "number" && Number.isInteger(number) && number > 0
    ? number
    : undefined;
}

export function parseCityId(value: unknown): number | undefined {
  return parsePositiveInt(value);
}

export function parsePage(value: unknown): number | undefined {
  return parsePositiveInt(value);
}

export function parseRegion(value: unknown): string | undefined {
  return typeof value === "string" && value !== "" ? value : undefined;
}
