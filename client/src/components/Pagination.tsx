type PaginationProps = {
  page: number;
  totalPages: number;
  onPageChange: (page: number) => void;
  disabled?: boolean;
};

const BUTTON_CLASS =
  "cursor-pointer rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 aria-disabled:cursor-not-allowed aria-disabled:text-slate-400 aria-disabled:hover:bg-white";

export function Pagination({
  page,
  totalPages,
  onPageChange,
  disabled = false,
}: PaginationProps) {
  if (totalPages <= 1) {
    return null;
  }

  const atStart = page <= 1;
  const atEnd = page >= totalPages;

  const buttonProps = (inactive: boolean, target: number) => ({
    type: "button" as const,
    className: BUTTON_CLASS,
    "aria-disabled": inactive,
    onClick: () => {
      if (!inactive) {
        onPageChange(target);
      }
    },
  });

  return (
    <nav
      aria-label="Lapozás"
      className="flex flex-wrap items-center justify-center gap-2"
    >
      <button {...buttonProps(disabled || atStart, 1)}>Első</button>
      <button {...buttonProps(disabled || atStart, page - 1)}>Előző</button>
      <span
        role="status"
        aria-live="polite"
        className="px-3 text-sm font-medium tabular-nums"
      >
        {page}. / {totalPages}. oldal
      </span>
      <button {...buttonProps(disabled || atEnd, page + 1)}>Következő</button>
      <button {...buttonProps(disabled || atEnd, totalPages)}>Utolsó</button>
    </nav>
  );
}
