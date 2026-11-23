import { useId, type ReactNode } from "react";
import { DateRangeFields } from "./DateRangeFields";
import { StatusMessage } from "./StatusMessage";

type WeatherQueryFormProps = {
  children: ReactNode;
  from: string;
  to: string;
  onDateRangeChange: (range: { from: string; to: string }) => void;
  onSubmit: () => void;
  isLoading: boolean;
  error: string | null;
};

export function WeatherQueryForm({
  children,
  from,
  to,
  onDateRangeChange,
  onSubmit,
  isLoading,
  error,
}: WeatherQueryFormProps) {
  const errorId = useId();

  const handleSubmit = (event: { preventDefault: () => void; }) => {
    event.preventDefault();
    onSubmit();
  };

  return (
    <form
      onSubmit={handleSubmit}
      className="space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
    >
      <div className="grid gap-4 sm:grid-cols-3">
        {children}
        <DateRangeFields
          from={from}
          to={to}
          onChange={onDateRangeChange}
          errorId={error === null ? undefined : errorId}
        />
      </div>
      <div className="flex flex-wrap items-center gap-4">
        <button
          type="submit"
          disabled={isLoading}
          className="cursor-pointer rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-sky-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-300"
        >
          {isLoading ? "Betöltés…" : "Lekérdezés"}
        </button>
        {error !== null && (
          <StatusMessage kind="error" id={errorId}>
            {error}
          </StatusMessage>
        )}
      </div>
    </form>
  );
}
