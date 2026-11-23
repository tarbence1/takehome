import { describe, expect, it } from "vitest";
import {
  convertModes,
  convertPrecipitation,
  convertTemperature,
  isUnitSystem,
  precipitationUnit,
  temperatureUnit,
} from "./units";

describe("convertTemperature", () => {
  it("°C-ból °F-ba vált", () => {
    expect(convertTemperature(0, "imperial")).toBe(32);
    expect(convertTemperature(100, "imperial")).toBe(212);
    expect(convertTemperature(-40, "imperial")).toBe(-40);
    expect(convertTemperature(3.4, "imperial")).toBe(38.12);
  });

  it("SI esetén változatlan", () => {
    expect(convertTemperature(3.4, "si")).toBe(3.4);
  });
});

describe("convertPrecipitation", () => {
  it("mm-ből inchre vált", () => {
    expect(convertPrecipitation(25.4, "imperial")).toBe(1);
    expect(convertPrecipitation(10, "imperial")).toBe(0.39);
  });

  it("SI esetén változatlan", () => {
    expect(convertPrecipitation(10, "si")).toBe(10);
  });
});

describe("mértékegység címkék", () => {
  it("rendszerenként a megfelelő", () => {
    expect(temperatureUnit("si")).toBe("°C");
    expect(temperatureUnit("imperial")).toBe("°F");
    expect(precipitationUnit("si")).toBe("mm");
    expect(precipitationUnit("imperial")).toBe("in");
  });
});

describe("isUnitSystem", () => {
  it("csak az ismert rendszereket fogadja el", () => {
    expect(isUnitSystem("si")).toBe(true);
    expect(isUnitSystem("imperial")).toBe(true);
    expect(isUnitSystem("metric")).toBe(false);
    expect(isUnitSystem(null)).toBe(false);
  });
});

describe("convertModes", () => {
  it("az átváltás után azonossá váló értékeket összevonja", () => {
    const toInches = (mm: number) => convertPrecipitation(mm, "imperial");

    expect(convertModes([0, 0.1, 25.4], toInches)).toEqual([0, 1]);
  });

  it("különböző értékeknél megtartja a sorrendet", () => {
    expect(convertModes([1, 2, 3], (value) => value * 2)).toEqual([2, 4, 6]);
  });

  it("üres listára üreset ad", () => {
    expect(convertModes([], (value) => value)).toEqual([]);
  });
});
