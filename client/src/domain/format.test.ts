import { describe, expect, it } from "vitest";
import {
  capitalizeFirst,
  formatDate,
  formatMeasurement,
  formatModes,
  formatNumber,
} from "./format";

const NBSP = " ";

describe("formatNumber", () => {
  it("tizedesvesszőt használ, felesleges nullák nélkül", () => {
    expect(formatNumber(3.4)).toBe("3,4");
    expect(formatNumber(2)).toBe("2");
  });

  it("legfeljebb 2 tizedest ír", () => {
    expect(formatNumber(1.236)).toBe("1,24");
  });

  it("sosem ír negatív nullát", () => {
    expect(formatNumber(-0.001)).toBe("0");
    expect(formatNumber(-0)).toBe("0");
  });

  it("a negatív előjel ASCII kötőjel (hu-HU, Node 20)", () => {
    expect(formatNumber(-1.25)).toBe("-1,25");
  });
});

describe("formatMeasurement", () => {
  it("érték és egység törhetetlen szóközzel", () => {
    expect(formatMeasurement(3.4, "°C")).toBe(`3,4${NBSP}°C`);
  });

  it("null esetén gondolatjel", () => {
    expect(formatMeasurement(null, "°C")).toBe("–");
  });
});

describe("formatDate", () => {
  it("YYYY-MM-DD → 2023. 01. 15.", () => {
    expect(formatDate("2023-01-15")).toBe("2023. 01. 15.");
  });

  it("nem függ az időzónától", () => {
    expect(formatDate("2023-12-31")).toBe("2023. 12. 31.");
  });
});

describe("formatModes", () => {
  const f = (v: number) => String(v);

  it("üres listára 'nincs', title nélkül", () => {
    expect(formatModes([], f)).toEqual({ label: "nincs", title: undefined });
  });

  it("pontosvesszővel elválasztva, csonkolás nélkül nincs title", () => {
    expect(formatModes([1, 2], f)).toEqual({ label: "1; 2", title: undefined });
    expect(formatModes([1, 2, 3], f)).toEqual({
      label: "1; 2; 3",
      title: undefined,
    });
  });

  it("a felesleget +N jelzi, a title-ben mind szerepel", () => {
    expect(formatModes([1, 2, 3, 4, 5], f)).toEqual({
      label: "1; 2; 3 +2",
      title: "1; 2; 3; 4; 5",
    });
    expect(formatModes([1, 2, 3, 4], f, 2)).toEqual({
      label: "1; 2 +2",
      title: "1; 2; 3; 4",
    });
  });
});

describe("capitalizeFirst", () => {
  it("nagybetűsíti az első betűt", () => {
    expect(capitalizeFirst("a(z) 'X' régió nem található")).toBe(
      "A(z) 'X' régió nem található",
    );
    expect(capitalizeFirst("érvénytelen dátum")).toBe("Érvénytelen dátum");
  });

  it("üres szövegre üreset ad", () => {
    expect(capitalizeFirst("")).toBe("");
  });
});
