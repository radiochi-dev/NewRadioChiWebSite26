import { router } from '@inertiajs/react'
import { useMemo, useState } from 'react'
import { FiChevronDown } from 'react-icons/fi'
import Button from './Button'
import Input from './Input'
import Select from './Select'

export default function FilterBar({ path, filters = [], sortFields = [], query = {}, compact = false }) {
    const [search, setSearch] = useState(query.search ?? '')
    const [sort, setSort] = useState(query.sort ?? '')
    const [direction, setDirection] = useState(query.direction ?? 'desc')
    const [perPage, setPerPage] = useState(String(query.perPage ?? 10))
    const [filterState, setFilterState] = useState(query.filters ?? {})
    const [isExpanded, setIsExpanded] = useState(false)

    const sortOptions = useMemo(
        () => [
            { value: '', label: 'Orden por defecto' },
            ...sortFields.map((field) => ({
                value: field.key,
                label: `Ordenar por ${field.label}`,
            })),
        ],
        [sortFields],
    )

    const submit = (event) => {
        event.preventDefault()

        router.get(
            path,
            {
                search,
                sort,
                direction,
                perPage,
                page: 1,
                filters: filterState,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        )
    }

    const reset = () => {
        setSearch('')
        setSort('')
        setDirection('desc')
        setPerPage('10')
        setFilterState({})
        setIsExpanded(false)

        router.get(
            path,
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        )
    }

    return (
        <form
            onSubmit={submit}
            className={
                compact
                    ? `rounded-[24px] border border-white/10 bg-white/[0.03] px-4 ${isExpanded ? 'pb-4 pt-4' : 'py-3'}`
                    : `rounded-[28px] border border-white/10 bg-white/[0.03] px-5 ${isExpanded ? 'pb-5 pt-5' : 'py-4'}`
            }
        >
            <div className={`flex flex-col ${isExpanded ? (compact ? 'gap-4' : 'gap-5') : 'gap-0'}`}>
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p className="text-sm font-semibold text-white">Filtros</p>
                    </div>

                    <button
                        type="button"
                        onClick={() => setIsExpanded((current) => !current)}
                        className="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-2 text-sm font-semibold text-white/75 transition hover:bg-white/[0.06] hover:text-white"
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
                        <div className={`grid gap-4 xl:grid-cols-[minmax(0,1.6fr)_repeat(4,minmax(0,1fr))] ${isExpanded ? 'pt-1' : 'pt-0'}`}>
                            <Input
                                label="Busqueda"
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Buscar por texto..."
                                hint={compact ? null : 'Se valida en backend y se conserva en querystring.'}
                            />

                            <Select
                                label="Orden"
                                value={sort}
                                onChange={(event) => setSort(event.target.value)}
                                options={sortOptions}
                            />

                            <Select
                                label="Direccion"
                                value={direction}
                                onChange={(event) => setDirection(event.target.value)}
                                options={[
                                    { value: 'desc', label: 'Descendente' },
                                    { value: 'asc', label: 'Ascendente' },
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

                            {filters.slice(0, 1).map((filter) => (
                                <Select
                                    key={filter.key}
                                    label={filter.label}
                                    value={filterState[filter.key] ?? ''}
                                    onChange={(event) =>
                                        setFilterState((current) => ({
                                            ...current,
                                            [filter.key]: event.target.value,
                                        }))
                                    }
                                    options={[
                                        { value: '', label: filter.placeholder },
                                        ...filter.options,
                                    ]}
                                />
                            ))}
                        </div>

                        {filters.length > 1 ? (
                            <div className="mt-4 grid gap-4 xl:grid-cols-3">
                                {filters.slice(1).map((filter) => (
                                    <Select
                                        key={filter.key}
                                        label={filter.label}
                                        value={filterState[filter.key] ?? ''}
                                        onChange={(event) =>
                                            setFilterState((current) => ({
                                                ...current,
                                                [filter.key]: event.target.value,
                                            }))
                                        }
                                        options={[
                                            { value: '', label: filter.placeholder },
                                            ...filter.options,
                                        ]}
                                    />
                                ))}
                            </div>
                        ) : null}

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
}
