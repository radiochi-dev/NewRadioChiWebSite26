import { router } from '@inertiajs/react'
import { useState } from 'react'
import { FiChevronDown } from 'react-icons/fi'
import BackofficeLayout from '../../../Layouts/BackofficeLayout'
import Button from '../ui/Button'
import EmptyState from '../ui/EmptyState'
import Input from '../ui/Input'
import Pagination from '../ui/Pagination'
import Select from '../ui/Select'

function normalizePreviewUrl(value) {
    if (typeof value !== 'string' || value.trim() === '') {
        return ''
    }

    if (/^(https?:\/\/|data:|\/)/i.test(value)) {
        return value
    }

    return `/${value.replace(/^\/+/, '')}`
}

function AssetPreview({ item }) {
    const previewUrl = normalizePreviewUrl(item.preview?.url ?? '')
    const badgeClass = {
        image: 'bg-cyan-400/15 text-cyan-100 border-cyan-300/20',
        video: 'bg-fuchsia-400/15 text-fuchsia-100 border-fuchsia-300/20',
        pdf: 'bg-amber-400/15 text-amber-100 border-amber-300/20',
        other: 'bg-white/10 text-white/70 border-white/10',
    }

    if (previewUrl) {
        return (
            <div className="relative aspect-[4/3] overflow-hidden bg-slate-950">
                <img src={previewUrl} alt={item.filename} className="h-full w-full object-cover" />
                <span className={`absolute left-3 top-3 inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] ${badgeClass[item.formatKey] ?? badgeClass.other}`}>
                    {item.formatLabel}
                </span>
            </div>
        )
    }

    return (
        <div className="relative flex aspect-[4/3] items-center justify-center bg-slate-950">
            <div className="text-center">
                <span className={`inline-flex rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] ${badgeClass[item.formatKey] ?? badgeClass.other}`}>
                    {item.formatLabel}
                </span>
                <p className="mt-4 text-sm text-white/55">Sin preview embebida disponible</p>
            </div>
        </div>
    )
}

function AssetCard({ item }) {
    return (
        <article className="overflow-hidden rounded-[28px] border border-white/10 bg-white/3 shadow-[0_20px_55px_rgba(0,0,0,0.28)]">
            <AssetPreview item={item} />

            <div className="space-y-5 px-5 py-5">
                <div>
                    <h3 className="truncate text-base font-semibold text-white">{item.filename}</h3>
                    <p className="mt-1 text-sm text-white/45">{item.mimeType}</p>
                </div>

                <dl className="grid gap-3 sm:grid-cols-2">
                    <div className="rounded-2xl border border-white/10 bg-black/20 px-3 py-3">
                        <dt className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/40">Tamano</dt>
                        <dd className="mt-2 text-sm font-medium text-white">{item.sizeHuman}</dd>
                    </div>
                    <div className="rounded-2xl border border-white/10 bg-black/20 px-3 py-3">
                        <dt className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/40">Formato</dt>
                        <dd className="mt-2 text-sm font-medium text-white">{item.formatLabel}</dd>
                    </div>
                    <div className="rounded-2xl border border-white/10 bg-black/20 px-3 py-3">
                        <dt className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/40">Dimensiones</dt>
                        <dd className="mt-2 text-sm font-medium text-white">{item.dimensions}</dd>
                    </div>
                    <div className="rounded-2xl border border-white/10 bg-black/20 px-3 py-3">
                        <dt className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/40">Actualizado</dt>
                        <dd className="mt-2 text-sm font-medium text-white">{item.updatedAt}</dd>
                    </div>
                </dl>

                <div>
                    <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/40">Pagina asignada</p>
                    <div className="mt-3 flex flex-wrap gap-2">
                        {item.assignedPages.length ? (
                            item.assignedPages.map((page) => (
                                <span
                                    key={`${item.id}-${page}`}
                                    className="inline-flex rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-medium text-white/80"
                                >
                                    {page}
                                </span>
                            ))
                        ) : (
                            <span className="inline-flex rounded-full border border-amber-300/15 bg-amber-400/10 px-3 py-1 text-xs font-medium text-amber-100">
                                Sin asignar
                            </span>
                        )}
                    </div>
                </div>

                <div className="flex justify-end">
                    <Button href={item.editHref} variant="ghost">
                        Editar asset
                    </Button>
                </div>
            </div>
        </article>
    )
}

export default function MediaAssetIndexScreen({
    title,
    description,
    breadcrumbs,
    actions,
    summaryCards,
    gallery,
    filters = [],
    capabilities,
}) {
    const [search, setSearch] = useState(gallery?.query?.search ?? '')
    const [assignedPage, setAssignedPage] = useState(gallery?.query?.filters?.assigned_page ?? '')
    const [format, setFormat] = useState(gallery?.query?.filters?.format ?? '')
    const [perPage, setPerPage] = useState(String(gallery?.query?.perPage ?? 10))
    const [isExpanded, setIsExpanded] = useState(false)

    const submit = (event) => {
        event.preventDefault()

        router.get(
            gallery.path,
            {
                search,
                page: 1,
                perPage,
                filters: {
                    assigned_page: assignedPage,
                    format,
                },
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        )
    }

    const reset = () => {
        setSearch('')
        setAssignedPage('')
        setFormat('')
        setPerPage('10')
        setIsExpanded(false)

        router.get(
            gallery.path,
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        )
    }

    const headerFilters = (
        <form
            onSubmit={submit}
            className={`rounded-[24px] border border-white/10 bg-white/3 px-4 ${isExpanded ? 'pb-4 pt-4' : 'py-3'}`}
        >
            <div className={`flex flex-col ${isExpanded ? 'gap-4' : 'gap-0'}`}>
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p className="text-sm font-semibold text-white">Filtros</p>
                    </div>

                    <button
                        type="button"
                        onClick={() => setIsExpanded((current) => !current)}
                        className="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/3 px-3 py-2 text-sm font-semibold text-white/75 transition hover:bg-white/6 hover:text-white"
                    >
                        <span>{isExpanded ? 'Ocultar filtros' : 'Mostrar filtros'}</span>
                        <FiChevronDown className={`h-4 w-4 transition-transform duration-300 ${isExpanded ? 'rotate-180' : ''}`} />
                    </button>
                </div>

                <div
                    aria-hidden={!isExpanded}
                    className={`grid overflow-hidden transition-[grid-template-rows,opacity,margin] duration-300 ease-out ${isExpanded ? 'mt-4 grid-rows-[1fr] opacity-100' : 'mt-0 grid-rows-[0fr] opacity-0 pointer-events-none'}`}
                >
                    <div className="min-h-0 overflow-hidden">
                        <div className={`grid gap-4 xl:grid-cols-[minmax(0,1.5fr)_repeat(3,minmax(0,1fr))] ${isExpanded ? 'pt-1' : 'pt-0'}`}>
                            <Input
                                label="Nombre"
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Buscar por nombre del archivo..."
                            />

                            <Select
                                label={filters[0]?.label ?? 'Pagina asignada'}
                                value={assignedPage}
                                onChange={(event) => setAssignedPage(event.target.value)}
                                options={[
                                    { value: '', label: filters[0]?.placeholder ?? 'Todas las paginas' },
                                    ...(filters[0]?.options ?? []),
                                ]}
                            />

                            <Select
                                label={filters[1]?.label ?? 'Formato'}
                                value={format}
                                onChange={(event) => setFormat(event.target.value)}
                                options={[
                                    { value: '', label: filters[1]?.placeholder ?? 'Todos los formatos' },
                                    ...(filters[1]?.options ?? []),
                                ]}
                            />

                            <Select
                                label="Items"
                                value={perPage}
                                onChange={(event) => setPerPage(event.target.value)}
                                options={[
                                    { value: '10', label: '10 por pagina' },
                                    { value: '25', label: '25 por pagina' },
                                    { value: '50', label: '50 por pagina' },
                                ]}
                            />
                        </div>

                        <div className="mt-4 flex flex-wrap items-center justify-end gap-3">
                            <Button type="button" variant="ghost" onClick={reset}>
                                Limpiar
                            </Button>
                            <Button type="submit" variant="primary">
                                Aplicar filtros
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    )

    return (
        <BackofficeLayout
            title={title}
            description={description}
            breadcrumbs={breadcrumbs}
            actions={actions}
            summaryCards={summaryCards}
            headerContent={headerFilters}
        >
            <div className="space-y-6">
                {!capabilities.canEdit ? (
                    <section className="rounded-[28px] border border-amber-400/20 bg-amber-400/10 px-5 py-4 text-sm text-amber-100">
                        Tu rol actual es de solo lectura. Puedes revisar la galeria y sus asignaciones, pero no editar assets.
                    </section>
                ) : null}

                {gallery.items.length ? (
                    <section className="grid gap-5 md:grid-cols-2 2xl:grid-cols-3">
                        {gallery.items.map((item) => (
                            <AssetCard key={item.id} item={item} />
                        ))}
                    </section>
                ) : (
                    <EmptyState {...gallery.emptyState} />
                )}

                <Pagination pagination={gallery.pagination} path={gallery.path} query={gallery.query} />
            </div>
        </BackofficeLayout>
    )
}
