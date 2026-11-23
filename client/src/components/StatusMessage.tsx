import type { ReactNode } from "react";

type StatusMessageProps = {
  kind: "info" | "error";
  id?: string;
  children: ReactNode;
};

const KIND_CLASS = {
  info: "border-slate-200 bg-white text-slate-600",
  error: "border-red-200 bg-red-50 text-red-800",
} as const;

export function StatusMessage({ kind, id, children }: StatusMessageProps) {
  return (
    <p
      id={id}
      role={kind === "error" ? "alert" : "status"}
      className={`rounded-lg border px-4 py-3 text-sm ${KIND_CLASS[kind]}`}
    >
      {children}
    </p>
  );
}
