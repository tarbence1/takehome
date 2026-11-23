import { Layout } from "./Layout";
import { StatusMessage } from "./StatusMessage";

export function RootPending() {
  return (
    <Layout>
      <StatusMessage kind="info">Betöltés…</StatusMessage>
    </Layout>
  );
}
