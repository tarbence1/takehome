export function roundTo2(value: number): number {
  const abs = Math.abs(value);
  const text = String(abs);
  const rounded = text.includes("e")
    ? Math.round(abs * 100) / 100
    : Number(`${Math.round(Number(`${text}e2`))}e-2`);
  const result = Math.sign(value) * rounded;
  return result === 0 ? 0 : result;
}
