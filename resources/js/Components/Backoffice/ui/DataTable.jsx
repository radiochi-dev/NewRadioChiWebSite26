import { router } from '@inertiajs/react'
import { useMemo, useState } from 'react'
import Button from './Button'
import EmptyState from './EmptyState'

export default function DataTable({ table }) {
    const { columns = [], rows = [], emptyState, bulkActions = [], sort = {}, path, query = {} } = table
    const [selectedRows, setSelectedRows] = useState([])
    const canSelect = bulkActions.length > 0 && rows.length > 0

    const allSelected = useMemo(
        () => rows.length > 0 && selectedRows.length === rows.length,
        [rows.length, selectedRows.length],
    )

    const toggleSort = (columnKey) => {
        router.get(
            path,
            {
                ...query,
                sort: columnKey,
                direction: sort.column === columnKey && sort.direction === 'asc' ? 'desc' : 'asc',
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        )
    }

    const toggleAll = () => {
        setSelectedRows(allSelected ? [] : rows.map((row) => row.id))
    }

    const toggleRow = (rowId) => {
        setSelectedRows((current) =>
            current.includes(rowId) ? current.filter((value) => value !== rowId) : [...current, rowId],
        )
    }

    return (
        <section className="overflow-hidden rounded-[28px] border border-white/10 bg-white/[0.03]">
            {bulkActions.length ? (
                <div className="flex flex-wrap items-center justify-between gap-4 border-b border-white/10 bg-white/[0.02] px-5 py-4">
                    <div>
                        <p className="text-sm font-semibold text-white">Acciones masivas</p>
                        <p className="mt-1 text-xs text-white/50">
                            {selectedRows.length
                                ? `${selectedRows.length} registro(s) seleccionados en la tabla`
                                : 'Selecciona registros para validar el patron de acciones masivas.'}
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {bulkActions.map((action) => (
                            <Button
                                key={action.label}
                                variant={action.variant}
                                disabled={!selectedRows.length || !action.enabled}
                            >
                                {action.label}
                            </Button>
                        ))}
                    </div>
                </div>
            ) : null}
            <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-white/10 text-left">
                    <thead className="bg-white/[0.02]">
                        <tr>
                            {canSelect ? (
                                <th className="px-5 py-4">
                                    <input
                                        type="checkbox"
                                        checked={allSelected}
                                        onChange={toggleAll}
                                        className="h-4 w-4 rounded border-white/20 bg-slate-950"
                                    />
                                </th>
                            ) : null}
                            {columns.map((column) => (
                                <th
                                    key={column.key}
                                    className="px-5 py-4 text-xs font-semibold uppercase tracking-[0.24em] text-white/45"
                                >
                                    <button
                                        type="button"
                                        onClick={() => toggleSort(column.key)}
                                        className="inline-flex items-center gap-2 text-left"
                                    >
                                        <span>{column.label}</span>
                                        {sort.column === column.key ? (
                                            <span className="text-cyan-200">{sort.direction === 'asc' ? '↑' : '↓'}</span>
                                        ) : null}
                                    </button>
                                </th>
                            ))}
                            <th className="px-5 py-4 text-xs font-semibold uppercase tracking-[0.24em] text-white/45">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {rows.length ? (
                            rows.map((row, rowIndex) => (
                                <tr key={row.id ?? rowIndex} className="border-t border-white/10">
                                    {canSelect ? (
                                        <td className="px-5 py-4">
                                            <input
                                                type="checkbox"
                                                checked={selectedRows.includes(row.id)}
                                                onChange={() => toggleRow(row.id)}
                                                className="h-4 w-4 rounded border-white/20 bg-slate-950"
                                            />
                                        </td>
                                    ) : null}
                                    {row.cells.map((cell) => (
                                        <td
                                            key={cell.key}
                                            className={`px-5 py-4 text-sm ${
                                                cell.align === 'right' ? 'text-right' : 'text-left'
                                            } text-white/80`}
                                        >
                                            {cell.badge ? (
                                                <span className="inline-flex rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-100">
                                                    {cell.value}
                                                </span>
                                            ) : (
                                                cell.value ?? '—'
                                            )}
                                        </td>
                                    ))}
                                    <td className="px-5 py-4">
                                        <div className="flex flex-wrap justify-end gap-2">
                                            {row.actions.map((action) => (
                                                <Button
                                                    key={`${row.id}-${action.label}`}
                                                    href={action.href}
                                                    variant={action.variant}
                                                    disabled={!action.enabled}
                                                >
                                                    {action.label}
                                                </Button>
                                            ))}
                                        </div>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan={Math.max(columns.length + (canSelect ? 2 : 1), 1)} className="px-5 py-6">
                                    <EmptyState {...emptyState} />
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </section>
    )
}
