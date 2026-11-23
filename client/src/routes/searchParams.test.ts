import { describe, expect, it } from "vitest";
import {
  parseCityId,
  parseDateRange,
  parsePage,
  parseRegion,
} from "./searchParams";

describe("parseDateRange", () => {
  it("érvényes dátumokat megtartja", () => {
    expect(parseDateRange({ from: "2023-01-01", to: "2023-01-31" })).toEqual({
      from: "2023-01-01",
      to: "2023-01-31",
    });
  });

  it("az érvénytelen értéket elhagyja", () => {
    expect(parseDateRange({ from: "nem dátum", to: "2023-01-31" })).toEqual({
      to: "2023-01-31",
    });
    expect(parseDateRange({ from: 20230101, to: "2023-02-30" })).toEqual({});
  });

  it("hiányzó értékre üres objektumot ad", () => {
    expect(parseDateRange({})).toEqual({});
  });
});

describe("parseCityId", () => {
  it("pozitív egészet elfogad számként és szövegként is", () => {
    expect(parseCityId(7)).toBe(7);
    expect(parseCityId("7")).toBe(7);
  });

  it("érvénytelen értékre undefined-ot ad", () => {
    expect(parseCityId("abc")).toBeUndefined();
    expect(parseCityId("1.5")).toBeUndefined();
    expect(parseCityId(0)).toBeUndefined();
    expect(parseCityId(-3)).toBeUndefined();
    expect(parseCityId("")).toBeUndefined();
    expect(parseCityId(null)).toBeUndefined();
  });

  it("hiányzó értékre undefined-ot ad", () => {
    expect(parseCityId(undefined)).toBeUndefined();
  });
});

describe("parseRegion", () => {
  it("nem üres szöveget megtart", () => {
    expect(parseRegion("Észak és Alföld")).toBe("Észak és Alföld");
  });

  it("üres vagy nem szöveges értéket elhagy", () => {
    expect(parseRegion("")).toBeUndefined();
    expect(parseRegion(5)).toBeUndefined();
    expect(parseRegion(["a"])).toBeUndefined();
    expect(parseRegion(undefined)).toBeUndefined();
  });
});

describe("parsePage", () => {
  it("pozitív egészet elfogad számként és szövegként is", () => {
    expect(parsePage(3)).toBe(3);
    expect(parsePage("3")).toBe(3);
  });

  it("érvénytelen vagy hiányzó értékre undefined-ot ad", () => {
    expect(parsePage("abc")).toBeUndefined();
    expect(parsePage("1.5")).toBeUndefined();
    expect(parsePage(0)).toBeUndefined();
    expect(parsePage(-1)).toBeUndefined();
    expect(parsePage(["1"])).toBeUndefined();
    expect(parsePage(undefined)).toBeUndefined();
  });
});
