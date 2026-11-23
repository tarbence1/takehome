import { isIsoDate } from "./dateRange";
import { roundTo2 } from "./rounding";

const numberFormat = new Intl.NumberFormat("hu-HU", {
  maximumFractionDigits: 2,
});

const dateFormat = new Intl.DateTimeFormat("hu-HU", {
  year: "numeric",
  month: "2-digit",
  day: "2-digit",
  timeZone: "UTC",
});

const MISSING = "–";
const NBSP = " ";

export function formatNumber(value: number): string {
  return numberFormat.format(roundTo2(value));
}

export function formatMeasurement(value: number | null, unit: string): string {
  return value === null ? MISSING : `${formatNumber(value)}${NBSP}${unit}`;
}

export function formatDate(isoDate: string): string {
  if (!isIsoDate(isoDate)) {
    return isoDate;
  }
  return dateFormat.format(new Date(`${isoDate}T00:00:00Z`));
}

export type FormattedModes = {
  label: string;
  title: string | undefined;
};

/**
 * A móduszokat legfeljebb `maxShown` elemig írja ki, a többit "+N" jelzi.
 * Csonkolás esetén a `title` az összes móduszt tartalmazza (tooltiphez).
 */
export function formatModes(
  values: readonly number[],
  formatValue: (value: number) => string,
  maxShown = 3,
): FormattedModes {
  if (values.length === 0) {
    return { label: "nincs", title: undefined };
  }

  const formatted = values.map(formatValue);
  const hidden = formatted.length - maxShown;
  if (hidden <= 0) {
    return { label: formatted.join("; "), title: undefined };
  }

  return {
    label: `${formatted.slice(0, maxShown).join("; ")} +${hidden}`,
    title: formatted.join("; "),
  };
}

export function capitalizeFirst(text: string): string {
  return text.charAt(0).toLocaleUpperCase("hu") + text.slice(1);
}
