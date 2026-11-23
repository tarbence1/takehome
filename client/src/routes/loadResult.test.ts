import { describe, expect, it } from "vitest";
import { ApiError } from "../api/httpClient";
import { loadWhenComplete } from "./loadResult";

describe("loadWhenComplete", () => {
  it("nem teljes keresésnél (null) idle", async () => {
    await expect(loadWhenComplete(null)).resolves.toEqual({ kind: "idle" });
  });

  it("sikeres lekérésnél ok", async () => {
    await expect(
      loadWhenComplete(() => Promise.resolve("adat")),
    ).resolves.toEqual({ kind: "ok", data: "adat" });
  });

  it("ApiError-t error eredménnyé alakítja", async () => {
    await expect(
      loadWhenComplete(() => Promise.reject(new ApiError(400, "Rossz"))),
    ).resolves.toEqual({ kind: "error", message: "Rossz" });
  });

  it("más kivételt továbbdob", async () => {
    await expect(
      loadWhenComplete(() => Promise.reject(new TypeError("hiba"))),
    ).rejects.toThrow(TypeError);
  });
});
