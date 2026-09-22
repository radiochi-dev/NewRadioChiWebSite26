import { router } from '@inertiajs/react'

export default function Pagination({ pagination, path, query = {} }) {
    const currentPage = pagination?.currentPage ?? 1
    const totalPages = pagination?.totalPages ?? 1

    const goTo = (page) => {
        router.get(
            path,
            {
                ...query,
                page,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        )
    }

    return (
        <div className="flex items-center justify-between gap-4 rounded-[24px] border border-white/10 bg-white/[0.03] px-4 py-3">
            <p className="text-sm text-white/55">
                Pagina <span className="font-semibold text-white">{currentPage}</span> de{' '}
                <span className="font-semibold text-white">{totalPages}</span>{' '}
                <span className="text-white/35">
                    ({pagination?.total ?? 0} registros, {pagination?.perPage ?? 0} por pagina)
                </span>
            </p>
            <div className="flex items-center gap-2">
                <button
                    type="button"
                    disabled={currentPage <= 1}
                    onClick={() => goTo(currentPage - 1)}
                    className="rounded-2xl border border-white/10 px-3 py-2 text-sm text-white/35 disabled:opacity-40"
                >
                    Anterior
                </button>
                <button
                    type="button"
                    disabled={currentPage >= totalPages}
                    onClick={() => goTo(currentPage + 1)}
                    className="rounded-2xl border border-white/10 px-3 py-2 text-sm text-white/35 disabled:opacity-40"
                >
                    Siguiente
                </button>
            </div>
        </div>
    )
}
