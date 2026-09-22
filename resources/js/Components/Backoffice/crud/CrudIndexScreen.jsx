import BackofficeLayout from '../../../Layouts/BackofficeLayout'
import DataTable from '../ui/DataTable'
import FilterBar from '../ui/FilterBar'
import Pagination from '../ui/Pagination'

export default function CrudIndexScreen({
    title,
    description,
    breadcrumbs,
    actions,
    summaryCards,
    filters,
    table,
    capabilities,
}) {
    return (
        <BackofficeLayout
            title={title}
            description={description}
            breadcrumbs={breadcrumbs}
            actions={actions}
            summaryCards={summaryCards}
        >
            <div className="space-y-6">
                <FilterBar path={table.path} filters={filters} sortFields={table.columns} query={table.query} />

                {!capabilities.canEdit ? (
                    <section className="rounded-[28px] border border-amber-400/20 bg-amber-400/10 px-5 py-4 text-sm text-amber-100">
                        Tu rol actual es de solo lectura. Puedes validar indices, filtros, payload y navegacion, pero las acciones de mutacion permanecen bloqueadas.
                    </section>
                ) : null}

                <DataTable table={table} />
                <Pagination pagination={table.pagination} path={table.path} query={table.query} />
            </div>
        </BackofficeLayout>
    )
}
