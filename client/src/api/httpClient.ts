/** Az API hibája; a 0-s státusz hálózati hibát jelent. */
export class ApiError extends Error {
  readonly status: number;

  constructor(status: number, message: string) {
    super(message);
    this.name = "ApiError";
    this.status = status;
  }
}

/** A `undefined` értékű paraméterek kimaradnak a kérésből. */
type QueryParams = Record<string, string | number | undefined>;

/** A szerver `{error: string}` törzséből kiolvassa az üzenetet, ha van. */
async function readErrorMessage(response: Response): Promise<string> {
  const body: unknown = await response.json().catch(() => null);
  if (
    typeof body === "object" &&
    body !== null &&
    "error" in body &&
    typeof body.error === "string"
  ) {
    return body.error;
  }
  return `Váratlan szerverhiba (${response.status})`;
}

/** GET kérés az API-hoz, a választ JSON-ként adja vissza. */
export async function getJson<T>(
  path: string,
  params?: QueryParams,
): Promise<T> {
  const query = params
    ? `?${new URLSearchParams(
        Object.entries(params)
          .filter(
            (entry): entry is [string, string | number] =>
              entry[1] !== undefined,
          )
          .map(([key, value]) => [key, String(value)]),
      )}`
    : "";

  let response: Response;
  try {
    response = await fetch(`${import.meta.env.VITE_API_URL}${path}${query}`);
  } catch {
    throw new ApiError(0, "A szerver nem érhető el");
  }

  if (!response.ok) {
    throw new ApiError(response.status, await readErrorMessage(response));
  }
  return (await response.json()) as T;
}
