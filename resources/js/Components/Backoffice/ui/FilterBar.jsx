import { router } from '@inertiajs/react'
import { useMemo, useState } from 'react'
import Button from './Button'
import Input from './Input'
import Select from './Select'

export default function FilterBar({ path, filters = [], sortFields = [], query = {} }) {
    const [search, setSearch] = useState(query.search ?? '')
    const [sort, setSort] = useState(query.sort ?? '')
    const [direction, setDirection] = useState(query.direction ?? 'desc')
    const [perPage, setPerPage] = useState(String(query.perPage ?? 2))
    const [filterState, setFilterState] = useState(query.filters ?? {})

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
        setPerPage('2')
        setFilterState({})

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
        <form onSubmit={submit} className="rounded-[28px] border border-white/10 bg-white/[0.03] p-5">
            <div className="flex flex-col gap-5">
                <div>
                    <p className="text-sm font-semibold text-white">Filtros, busqueda y ordenacion</p>
                    <p className="mt-1 text-sm text-white/55">
                        Patron reusable con querystring validada en backend para indices CRUD del nuevo backoffice.
                    </p>
                </div>

                <div className="grid gap-4 xl:grid-cols-[minmax(0,1.6fr)_repeat(4,minmax(0,1fr))]">
                    <Input
                        label="Busqueda"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Buscar por texto..."
                        hint="Se valida en backend y se conserva en querystring."
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
                        label="Resultados"
                        value={perPage}
                        onChange={(event) => setPerPage(event.target.value)}
                        options={[
                            { value: '2', label: '2 por pagina' },
                            { value: '4', label: '4 por pagina' },
                            { value: '8', label: '8 por pagina' },
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
                    <div className="grid gap-4 xl:grid-cols-3">
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

                <div className="flex flex-wrap items-center justify-end gap-3">
                    <Button type="button" variant="ghost" onClick={reset}>
                        Limpiar
                    </Button>
                    <Button type="submit" variant="primary">
                        Aplicar filtros
                    </Button>
                </div>
            </div>
        </form>
    )
}
