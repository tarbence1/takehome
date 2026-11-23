import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { ApiError, getJson } from "./httpClient";

const jsonResponse = (body: unknown, status = 200) =>
  new Response(JSON.stringify(body), {
    status,
    headers: { "Content-Type": "application/json" },
  });

const stubFetch = (impl: () => Promise<Response>) => {
  const fetchMock = vi.fn(impl);
  vi.stubGlobal("fetch", fetchMock);
  return fetchMock;
};

beforeEach(() => {
  vi.stubEnv("VITE_API_URL", "http://api.test");
});

afterEach(() => {
  vi.unstubAllGlobals();
  vi.unstubAllEnvs();
});

describe("getJson", () => {
  it("a parse-olt választ adja vissza siker esetén", async () => {
    stubFetch(async () => jsonResponse([{ id: 1 }]));

    await expect(getJson<{ id: number }[]>("/cities")).resolves.toEqual([
      { id: 1 },
    ]);
  });

  it("paraméterek nélkül nem fűz kérdőjelet az URL-hez", async () => {
    const fetchMock = stubFetch(async () => jsonResponse([]));

    await getJson("/cities");

    expect(fetchMock).toHaveBeenCalledWith("http://api.test/cities");
  });

  it("a szerver hibaüzenetével ApiError-t dob", async () => {
    stubFetch(async () =>
      jsonResponse({ error: "a(z) 'X' régió nem található" }, 404),
    );

    const error = await getJson("/weather/region").catch((e: unknown) => e);

    expect(error).toBeInstanceOf(ApiError);
    expect((error as ApiError).status).toBe(404);
    expect((error as ApiError).message).toBe("a(z) 'X' régió nem található");
  });

  it("nem-JSON hibatörzsnél általános üzenetet ad", async () => {
    stubFetch(async () => new Response("<html>boom</html>", { status: 500 }));

    const error = await getJson("/cities").catch((e: unknown) => e);

    expect(error).toBeInstanceOf(ApiError);
    expect((error as ApiError).status).toBe(500);
    expect((error as ApiError).message).toBe("Váratlan szerverhiba (500)");
  });

  it("string error mező nélküli JSON törzsnél is általános üzenetet ad", async () => {
    stubFetch(async () => jsonResponse({ error: { code: 1 } }, 500));

    const error = await getJson("/cities").catch((e: unknown) => e);

    expect((error as ApiError).message).toBe("Váratlan szerverhiba (500)");
  });

  it("hálózati hibánál 0-s státuszú ApiError-t dob", async () => {
    stubFetch(async () => {
      throw new TypeError("Failed to fetch");
    });

    const error = await getJson("/cities").catch((e: unknown) => e);

    expect(error).toBeInstanceOf(ApiError);
    expect((error as ApiError).status).toBe(0);
    expect((error as ApiError).message).toBe("A szerver nem érhető el");
  });
});
