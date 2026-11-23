import { describe, expect, it } from "vitest";
import { roundTo2 } from "./rounding";

describe("roundTo2", () => {
  it("kezeli a lebegőpontos csapdákat", () => {
    expect(roundTo2(1.005)).toBe(1.01);
    expect(roundTo2(2.345)).toBe(2.35);
  });

  it("nagyobb számoknál is helyes", () => {
    expect(roundTo2(10.075)).toBe(10.08);
    expect(roundTo2(-10.075)).toBe(-10.08);
    expect(roundTo2(38.125)).toBe(38.13);
  });

  it("szimmetrikus negatív értékekre", () => {
    expect(roundTo2(-1.005)).toBe(-1.01);
  });

  it("nem ad vissza negatív nullát", () => {
    const result = roundTo2(-0.001);
    expect(result).toBe(0);
    expect(Object.is(result, -0)).toBe(false);
  });
});
