import { useId } from "react";
import { INPUT_CLASS, LABEL_CLASS } from "./fieldStyles";

export type SelectOption<TValue> = { value: TValue; label: string };

type SelectFieldProps<TValue extends string | number> = {
  label: string;
  value: TValue;
  options: readonly SelectOption<TValue>[];
  onChange: (value: TValue) => void;
};

export function SelectField<TValue extends string | number>({
  label,
  value,
  options,
  onChange,
}: SelectFieldProps<TValue>) {
  const id = useId();

  return (
    <div>
      <label htmlFor={id} className={LABEL_CLASS}>
        {label}
      </label>
      <select
        id={id}
        value={String(value)}
        onChange={(event) => {
          const option = options.find(
            (candidate) => String(candidate.value) === event.target.value,
          );
          if (option) {
            onChange(option.value);
          }
        }}
        className={INPUT_CLASS}
      >
        {options.map((option) => (
          <option key={String(option.value)} value={String(option.value)}>
            {option.label}
          </option>
        ))}
      </select>
    </div>
  );
}
