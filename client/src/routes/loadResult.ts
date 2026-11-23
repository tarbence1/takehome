import { ApiError } from "../api/httpClient";

export type LoadResult<T> =
  | { kind: "idle" }
  | { kind: "ok"; data: T }
  | { kind: "error"; message: string };


export async function loadWhenComplete<T>(
  load: (() => Promise<T>) | null,
): Promise<LoadResult<T>> {
  if (load === null) {
    return { kind: "idle" };
  }
  try {
    return { kind: "ok", data: await load() };
  } catch (error) {
    if (error instanceof ApiError) {
      return { kind: "error", message: error.message };
    }
    throw error;
  }
}
