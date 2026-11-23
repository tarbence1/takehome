import { useState, type ReactNode } from "react";
import {
  rootRouteId,
  useNavigate,
  useRouter,
  useRouterState,
} from "@tanstack/react-router";
import { SelectField, type SelectOption } from "../components/SelectField";
import { StatusMessage } from "../components/StatusMessage";
import { WeatherQueryForm } from "../components/WeatherQueryForm";
import { validateDateRange } from "../domain/dateRange";
import { capitalizeFirst } from "../domain/format";
import type { LoadResult } from "./loadResult";

const DEFAULT_FROM = "2023-12-01";
const DEFAULT_TO = "2023-12-31";

export type WeatherQuery<TSelection> = {
  selection: TSelection;
  from: string;
  to: string;
};

type WeatherQueryPageProps<TSelection extends string | number, TReport> = {
  selectionLabel: string;
  options: readonly SelectOption<TSelection>[];
  committed: Partial<WeatherQuery<TSelection>>;
  onSubmit: (query: WeatherQuery<TSelection>) => void;
  result: LoadResult<TReport>;
  idleMessage: string;
  renderReport: (
    report: TReport,
    isLoading: boolean,
    goToPage: (page: number) => void,
  ) => ReactNode;
};

type QueryFormSectionProps<TSelection extends string | number> = Pick<
  WeatherQueryPageProps<TSelection, unknown>,
  "selectionLabel" | "options" | "onSubmit"
> & {
  initial: WeatherQuery<TSelection>;
  isLoading: boolean;
  committed: WeatherQuery<TSelection> | null;
};

function QueryFormSection<TSelection extends string | number>({
  selectionLabel,
  options,
  initial,
  isLoading,
  committed,
  onSubmit,
}: QueryFormSectionProps<TSelection>) {
  const router = useRouter();
  const [selection, setSelection] = useState(initial.selection);
  const [range, setRange] = useState({ from: initial.from, to: initial.to });
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = () => {
    const validationError = validateDateRange(range.from, range.to);
    setError(validationError);
    if (validationError !== null) {
      return;
    }

    const query = { selection, ...range };
    const isUnchanged =
      committed !== null &&
      committed.selection === query.selection &&
      committed.from === query.from &&
      committed.to === query.to;
    if (isUnchanged) {
      void router.invalidate({
        filter: (match) => match.routeId !== rootRouteId,
      });
    } else {
      onSubmit(query);
    }
  };

  return (
    <WeatherQueryForm
      from={range.from}
      to={range.to}
      onDateRangeChange={setRange}
      onSubmit={handleSubmit}
      isLoading={isLoading}
      error={error}
    >
      <SelectField
        label={selectionLabel}
        value={selection}
        options={options}
        onChange={setSelection}
      />
    </WeatherQueryForm>
  );
}

export function WeatherQueryPage<TSelection extends string | number, TReport>({
  selectionLabel,
  options,
  committed,
  onSubmit,
  result,
  idleMessage,
  renderReport,
}: WeatherQueryPageProps<TSelection, TReport>) {
  const isLoading = useRouterState({ select: (state) => state.isLoading });
  const navigate = useNavigate();

  const goToPage = (page: number) =>
    void navigate({
      to: ".",
      search: (prev) => ({ ...prev, page: page === 1 ? undefined : page }),
    });

  if (options.length === 0) {
    return (
      <StatusMessage kind="error">Nincs elérhető város/régió.</StatusMessage>
    );
  }

  const isKnown = (value: TSelection | undefined): value is TSelection =>
    options.some((option) => option.value === value);

  const committedQuery =
    isKnown(committed.selection) &&
    committed.from !== undefined &&
    committed.to !== undefined
      ? {
          selection: committed.selection,
          from: committed.from,
          to: committed.to,
        }
      : null;

  const initial = {
    selection: isKnown(committed.selection)
      ? committed.selection
      : options[0].value,
    from: committed.from ?? DEFAULT_FROM,
    to: committed.to ?? DEFAULT_TO,
  };

  return (
    <section className="space-y-6">
      <QueryFormSection
        key={JSON.stringify(initial)}
        selectionLabel={selectionLabel}
        options={options}
        initial={initial}
        isLoading={isLoading}
        committed={committedQuery}
        onSubmit={onSubmit}
      />
      <div
        aria-busy={isLoading}
        className={`transition-opacity ${isLoading ? "opacity-60" : ""}`}
      >
        {result.kind === "idle" && (
          <StatusMessage kind="info">{idleMessage}</StatusMessage>
        )}
        {result.kind === "error" && (
          <StatusMessage kind="error">
            {capitalizeFirst(result.message)}
          </StatusMessage>
        )}
        {result.kind === "ok" && renderReport(result.data, isLoading, goToPage)}
      </div>
    </section>
  );
}
