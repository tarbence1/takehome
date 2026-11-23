export const DATA_START = "1901-01-01";
export const DATA_END = "2023-12-31";

const ISO_DATE = /^(\d{4})-(\d{2})-(\d{2})$/;

function parseIsoDate(value: string): Date | null {
  const match = ISO_DATE.exec(value);
  if (!match) {
    return null;
  }

  const [year, month, day] = match.slice(1).map(Number);
  const date = new Date(Date.UTC(year, month - 1, day));
  const isRealDay =
    date.getUTCFullYear() === year &&
    date.getUTCMonth() === month - 1 &&
    date.getUTCDate() === day;
  return isRealDay ? date : null;
}

export function isIsoDate(value: unknown): value is string {
  return typeof value === "string" && parseIsoDate(value) !== null;
}

export function validateDateRange(from: string, to: string): string | null {
  const fromDate = parseIsoDate(from);
  const toDate = parseIsoDate(to);
  if (fromDate === null || toDate === null) {
    return "Érvénytelen dátum (ÉÉÉÉ-HH-NN formátum szükséges)";
  }
  if (from < DATA_START || to > DATA_END) {
    return `Az adatok ${DATA_START} és ${DATA_END} között érhetők el`;
  }
  if (fromDate > toDate) {
    return "A kezdő dátum nem lehet későbbi, mint a záró dátum";
  }

  return null;
}
