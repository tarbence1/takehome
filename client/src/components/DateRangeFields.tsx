import { useId } from "react";
import { DATA_END, DATA_START } from "../domain/dateRange";
import { INPUT_CLASS, LABEL_CLASS } from "./fieldStyles";

type DateRange = { from: string; to: string };

type DateRangeFieldsProps = DateRange & {
  onChange: (range: DateRange) => void;
  /** A hibaüzenet elem azonosítója; ha meg van adva, a mezők érvénytelenek. */
  errorId?: string;
};

export function DateRangeFields({
  from,
  to,
  onChange,
  errorId,
}: DateRangeFieldsProps) {
  const fromId = useId();
  const toId = useId();

  return (
    <>
      <div>
        <label htmlFor={fromId} className={LABEL_CLASS}>
          Kezdő dátum
        </label>
        <input
          id={fromId}
          type="date"
          value={from}
          min={DATA_START}
          max={DATA_END}
          onChange={(event) => onChange({ from: event.target.value, to })}
          aria-invalid={errorId !== undefined}
          aria-describedby={errorId}
          className={INPUT_CLASS}
        />
      </div>
      <div>
        <label htmlFor={toId} className={LABEL_CLASS}>
          Záró dátum
        </label>
        <input
          id={toId}
          type="date"
          value={to}
          min={DATA_START}
          max={DATA_END}
          onChange={(event) => onChange({ from, to: event.target.value })}
          aria-invalid={errorId !== undefined}
          aria-describedby={errorId}
          className={INPUT_CLASS}
        />
      </div>
    </>
  );
}
