import type { ReactNode } from "react";
import { Link, Outlet } from "@tanstack/react-router";
import { UnitToggle } from "../units/UnitToggle";

const NAV_LINKS = [
  { to: "/city", label: "Város" },
  { to: "/region", label: "Régió" },
] as const;

const NAV_LINK_CLASS =
  "rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 data-[status=active]:bg-sky-50 data-[status=active]:text-sky-700";

type LayoutProps = {
  /** Hiba esetén a tartalom helyett megjelenő elem; egyébként az aktív route. */
  children?: ReactNode;
};

export function Layout({ children }: LayoutProps) {
  return (
    <div className="min-h-screen bg-slate-50 text-slate-900">
      <header className="border-b border-slate-200 bg-white">
        <div className="mx-auto flex max-w-5xl flex-wrap items-center gap-x-6 gap-y-3 px-4 py-3">
          <h1 className="text-lg font-semibold">Időjárás kimutatás</h1>
          <nav aria-label="Főmenü" className="flex gap-1">
            {NAV_LINKS.map(({ to, label }) => (
              <Link
                key={to}
                to={to}
                search={(prev) => ({ units: prev.units })}
                className={NAV_LINK_CLASS}
              >
                {label}
              </Link>
            ))}
          </nav>
          <div className="ml-auto">
            <UnitToggle />
          </div>
        </div>
      </header>
      <main className="mx-auto max-w-5xl px-4 py-6">
        {children ?? <Outlet />}
      </main>
    </div>
  );
}
