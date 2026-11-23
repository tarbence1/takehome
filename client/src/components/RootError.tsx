import { useRouter } from "@tanstack/react-router";
import { ApiError } from "../api/httpClient";
import { capitalizeFirst } from "../domain/format";
import { Layout } from "./Layout";
import { StatusMessage } from "./StatusMessage";

export function RootError({ error }: { error: unknown }) {
  const router = useRouter();

  return (
    <Layout>
      <div className="space-y-4">
        <StatusMessage kind="error">
          Váratlan hiba történt.
          {error instanceof ApiError && ` ${capitalizeFirst(error.message)}`}
        </StatusMessage>
        <button
          type="button"
          onClick={() => void router.invalidate()}
          className="cursor-pointer rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-sky-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2"
        >
          Újrapróbálás
        </button>
      </div>
    </Layout>
  );
}
