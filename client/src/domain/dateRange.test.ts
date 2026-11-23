import { describe, expect, it } from "vitest";
import {
  DATA_END,
  DATA_START,
  isIsoDate,
  validateDateRange,
} from "./dateRange";

describe("isIsoDate", () => {
  it("elfogadja a valós YYYY-MM-DD napot", () => {
    expect(isIsoDate("2023-01-01")).toBe(true);
    expect(isIsoDate("2020-02-29")).toBe(true);
  });

  it("elutasítja a nem létező és rosszul formázott értékeket", () => {
    expect(isIsoDate("2023-02-30")).toBe(false);
    expect(isIsoDate("2023-1-1")).toBe(false);
    expect(isIsoDate("2023-01-01T00:00")).toBe(false);
    expect(isIsoDate("")).toBe(false);
    expect(isIsoDate(42)).toBe(false);
    expect(isIsoDate(null)).toBe(false);
  });
});

describe("konstansok", () => {
  it("az adatkészlet határai", () => {
    expect(DATA_START).toBe("1901-01-01");
    expect(DATA_END).toBe("2023-12-31");
  });
});

describe("validateDateRange", () => {
  it("érvényes tartományra null", () => {
    expect(validateDateRange("2000-01-01", "2010-01-01")).toBeNull();
    expect(validateDateRange("2023-05-05", "2023-05-05")).toBeNull();
    expect(validateDateRange(DATA_START, "1911-01-01")).toBeNull();
  });

  it("a kezdés nem lehet a vég után", () => {
    expect(validateDateRange("2010-01-02", "2010-01-01")).toContain(
      "nem lehet későbbi",
    );
  });

  it("a hosszú tartomány is érvényes, amíg az adatkészleten belül van", () => {
    expect(validateDateRange(DATA_START, DATA_END)).toBeNull();
  });

  it("az adatkészleten kívüli dátum hiba", () => {
    expect(validateDateRange("1900-12-31", "1901-01-05")).toContain(
      "között érhetők el",
    );
    expect(validateDateRange("2023-12-30", "2024-01-01")).toContain(
      "között érhetők el",
    );
  });

  it("érvénytelen dátum hiba", () => {
    expect(validateDateRange("2023-02-30", "2023-03-01")).toContain(
      "Érvénytelen dátum",
    );
    expect(validateDateRange("", "2023-03-01")).toContain("Érvénytelen dátum");
  });
});
