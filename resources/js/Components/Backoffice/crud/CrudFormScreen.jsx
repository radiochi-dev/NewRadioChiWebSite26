import { useEffect, useState } from 'react'
import { useForm } from '@inertiajs/react'
import BackofficeLayout from '../../../Layouts/BackofficeLayout'
import Button from '../ui/Button'
import CrudActionCard from './CrudActionCard'
import CrudFieldRenderer from './CrudFieldRenderer'

function Panel({ title, description, children }) {
    return (
        <section className="rounded-[28px] border border-white/10 bg-white/[0.03] p-6">
            <div className="flex flex-col gap-2">
                <p className="text-lg font-semibold text-white">{title}</p>
                {description ? <p className="text-sm leading-6 text-white/60">{description}</p> : null}
            </div>
            <div className="mt-5">{children}</div>
        </section>
    )
}

function RelationManagerPanel({ item }) {
    return (
        <div className="rounded-2xl border border-white/10 bg-black/20 p-4">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p className="text-sm font-semibold text-white">{item.label}</p>
                    <p className="mt-2 text-sm leading-6 text-white/60">{item.description}</p>
                </div>
                {item.createHref ? (
                    <Button href={item.createHref} variant="ghost">
                        {item.createLabel ?? 'Nueva relacion'}
                    </Button>
                ) : null}
            </div>

            {item.items?.length ? (
                <div className="mt-4 space-y-3">
                    {item.items.map((record) => (
                        <div key={record.id} className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.02] px-4 py-3">
                            <div>
                                <p className="text-sm font-semibold text-white">{record.label}</p>
                                {record.meta ? <p className="mt-1 text-xs text-white/50">{record.meta}</p> : null}
                            </div>
                            {record.href ? (
                                <Button href={record.href} variant="secondary">
                                    Abrir
                                </Button>
                            ) : null}
                        </div>
                    ))}
                </div>
            ) : (
                <div className="mt-4 rounded-2xl border border-dashed border-white/10 bg-white/[0.02] px-4 py-3 text-sm text-white/50">
                    Todavia no hay registros relacionados.
                </div>
            )}
        </div>
    )
}

function TabbedEditorForm({ tab, capabilities, mediaLibrary, mediaUploadUrl }) {
    const editorForm = useForm(tab.editor?.defaults ?? {})

    const submit = (event) => {
        event.preventDefault()

        editorForm.post(tab.editor.action, {
            forceFormData: true,
            preserveScroll: true,
        })
    }

    return (
        <form onSubmit={submit} className="space-y-5">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p className="text-sm font-semibold text-white">{tab.label}</p>
                    {tab.meta ? <p className="mt-1 text-xs text-white/50">{tab.meta}</p> : null}
                </div>
                <div className="flex flex-wrap gap-2">
                    {tab.editor?.deleteAction ? (
                        <Button
                            href={tab.editor.deleteAction.href}
                            method={tab.editor.deleteAction.method ?? 'post'}
                            variant="ghost"
                            disabled={!capabilities.canSubmit}
                        >
                            {tab.editor.deleteAction.label}
                        </Button>
                    ) : null}
                    <Button type="submit" variant="primary" disabled={!capabilities.canSubmit || editorForm.processing}>
                        {editorForm.processing ? 'Guardando...' : tab.editor?.submitLabel ?? 'Guardar'}
                    </Button>
                </div>
            </div>

            <div className="grid gap-4 xl:grid-cols-2">
                {(tab.editor?.fields ?? []).map((field) => (
                    <CrudFieldRenderer
                        key={`${tab.id}-${field.key}`}
                        field={field}
                        form={editorForm}
                        disabled={!capabilities.canSubmit}
                        mediaLibrary={mediaLibrary}
                        mediaUploadUrl={mediaUploadUrl}
                    />
                ))}
            </div>
        </form>
    )
}

function TabbedRelationManagerPanel({ item, capabilities, mediaLibrary, mediaUploadUrl }) {
    const tabs = item.tabs ?? []
    const fallbackTabId = tabs[0]?.id ?? null
    const requestedTabId = item.initialActiveTab && tabs.some((tab) => tab.id === item.initialActiveTab)
        ? item.initialActiveTab
        : fallbackTabId
    const [activeTabId, setActiveTabId] = useState(requestedTabId)

    useEffect(() => {
        setActiveTabId(requestedTabId)
    }, [requestedTabId])

    const activeTab = tabs.find((tab) => tab.id === activeTabId) ?? tabs[0] ?? null

    return (
        <div className="rounded-2xl border border-white/10 bg-black/20 p-4">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p className="text-sm font-semibold text-white">{item.label}</p>
                    <p className="mt-2 text-sm leading-6 text-white/60">{item.description}</p>
                </div>
                {item.createAction ? (
                    <Button
                        href={item.createAction.href}
                        method={item.createAction.method ?? 'post'}
                        variant="secondary"
                        disabled={!capabilities.canSubmit}
                    >
                        {item.createAction.label}
                    </Button>
                ) : null}
            </div>

            {tabs.length ? (
                <div className="mt-4 space-y-4">
                    <div className="flex flex-wrap gap-2">
                        {tabs.map((tab) => (
                            <Button
                                key={tab.id}
                                type="button"
                                onClick={() => setActiveTabId(tab.id)}
                                variant="ghost"
                                className={tab.id === activeTab?.id ? '!border-cyan-100 !bg-cyan-300 !text-slate-950' : ''}
                            >
                                {tab.label}
                            </Button>
                        ))}
                    </div>

                    {activeTab ? (
                        <div className="rounded-2xl border border-white/10 bg-white/[0.02] p-4">
                            <TabbedEditorForm
                                key={activeTab.id}
                                tab={activeTab}
                                capabilities={capabilities}
                                mediaLibrary={mediaLibrary}
                                mediaUploadUrl={mediaUploadUrl}
                            />
                        </div>
                    ) : null}
                </div>
            ) : null}

            {item.items?.length ? (
                <div className="mt-4 space-y-3">
                    {item.items.map((record) => (
                        <div key={record.id} className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.02] px-4 py-3">
                            <div>
                                <p className="text-sm font-semibold text-white">{record.label}</p>
                                {record.meta ? <p className="mt-1 text-xs text-white/50">{record.meta}</p> : null}
                            </div>
                            {record.href ? (
                                <Button href={record.href} variant="secondary">
                                    Abrir
                                </Button>
                            ) : null}
                        </div>
                    ))}
                </div>
            ) : null}
        </div>
    )
}

function LocaleActionsBar({ actions = [] }) {
    if (!actions.length) {
        return null
    }

    const explicitActiveAction = actions.find((action) => action.variant === 'primary')
    const fallbackActiveAction = explicitActiveAction
        ?? actions.find((action) => action.label?.trim().toUpperCase().startsWith('ES'))
    const normalizedActions = actions.map((action) => ({
        ...action,
        isActive: fallbackActiveAction === action,
    }))

    return (
        <section className="rounded-[28px] border border-cyan-400/15 bg-cyan-400/[0.05] p-4">
            <div className="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p className="text-sm font-semibold text-white">Idiomas</p>
                    <p className="mt-1 text-xs leading-6 text-white/55">
                        Cambia el locale activo del contenido dentro de este mismo contenedor.
                    </p>
                </div>
                <div className="flex flex-wrap gap-2">
                    {normalizedActions.map((action) => (
                        <Button
                            key={`${action.label}-${action.href ?? 'button'}`}
                            href={action.href}
                            variant={action.variant}
                            disabled={action.disabled}
                            external={action.external}
                            aria-current={action.isActive ? 'page' : undefined}
                            className={
                                action.isActive
                                    ? '!border-cyan-100 !bg-cyan-300 !text-slate-950 shadow-[0_0_0_1px_rgba(207,250,254,0.65)]'
                                    : ''
                            }
                        >
                            {action.label}
                        </Button>
                    ))}
                </div>
            </div>
        </section>
    )
}

export default function CrudFormScreen({
    title,
    description,
    breadcrumbs,
    actions,
    summaryCards,
    form,
    capabilities,
}) {
    const inertiaForm = useForm(form.defaults)

    const submit = (event) => {
        event.preventDefault()
        inertiaForm.post(form.action, {
            forceFormData: true,
            preserveScroll: true,
        })
    }

    return (
        <BackofficeLayout
            title={title}
            description={description}
            breadcrumbs={breadcrumbs}
            actions={actions}
            summaryCards={summaryCards}
        >
            <div className="space-y-6">
                <LocaleActionsBar actions={form.localeActions ?? []} />

                {form.tabbedEditors?.length ? (
                    <Panel
                        title={form.relationManagersTitle ?? 'Elementos relacionados'}
                        description={form.relationManagersDescription ?? 'Recursos vinculados al registro actual.'}
                    >
                        <div className="space-y-4">
                            {form.tabbedEditors.map((item) => (
                                <TabbedRelationManagerPanel
                                    key={item.label}
                                    item={item}
                                    capabilities={capabilities}
                                    mediaLibrary={form.mediaLibrary ?? []}
                                    mediaUploadUrl={form.mediaUploadUrl ?? null}
                                />
                            ))}
                        </div>
                    </Panel>
                ) : null}

                <form onSubmit={submit} className="space-y-6">
                    {form.sections.map((section) => (
                        <Panel key={section.title} title={section.title} description={section.description}>
                            <div className="grid gap-4 xl:grid-cols-2">
                                {section.fields.map((field) => (
                                    <CrudFieldRenderer
                                        key={field.key}
                                        field={field}
                                        form={inertiaForm}
                                        disabled={!capabilities.canSubmit}
                                        mediaLibrary={form.mediaLibrary ?? []}
                                        mediaUploadUrl={form.mediaUploadUrl ?? null}
                                    />
                                ))}
                            </div>
                        </Panel>
                    ))}

                    {form.relationManagers.length ? (
                        <Panel
                            title={form.relationManagersTitle ?? 'Elementos relacionados'}
                            description={form.relationManagersDescription ?? 'Recursos vinculados al registro actual.'}
                        >
                            <div className="grid gap-4 xl:grid-cols-2">
                                {form.relationManagers.map((item) => (
                                    <RelationManagerPanel key={item.label} item={item} />
                                ))}
                            </div>
                        </Panel>
                    ) : null}

                    {form.specialActions.length ? (
                        <Panel
                            title="Acciones especiales de negocio"
                            description="Patron reusable para colas, relation managers y payloads operativos que no son CRUD basico."
                        >
                            <div className="grid gap-4 xl:grid-cols-2">
                                {form.specialActions.map((action) => (
                                    <CrudActionCard key={action.slug} action={action} />
                                ))}
                            </div>
                        </Panel>
                    ) : null}

                    {form.dangerousActions.length ? (
                        <Panel
                            title="Zona de acciones peligrosas"
                            description="Patron reusable con confirmacion manual, permisos y feedback por flash messages."
                        >
                            <div className="grid gap-4 xl:grid-cols-2">
                                {form.dangerousActions.map((action) => (
                                    <CrudActionCard key={action.slug} action={action} />
                                ))}
                            </div>
                        </Panel>
                    ) : null}

                    {!form.hideSubmit ? (
                        <section className="flex flex-wrap items-center justify-end gap-3">
                            <Button href={breadcrumbs[breadcrumbs.length - 2]?.href} variant="ghost">
                                Cancelar
                            </Button>
                            <Button type="submit" variant="primary" disabled={!capabilities.canSubmit || inertiaForm.processing}>
                                {inertiaForm.processing ? 'Guardando...' : form.submitLabel ?? 'Guardar'}
                            </Button>
                        </section>
                    ) : null}
                </form>
            </div>
        </BackofficeLayout>
    )
}
