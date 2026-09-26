import { useForm } from '@inertiajs/react'
import BackofficeLayout from '../../../Layouts/BackofficeLayout'
import Button from '../ui/Button'
import CrudFieldRenderer from '../crud/CrudFieldRenderer'

function Panel({ title, description, children }) {
    return (
        <section className="rounded-[28px] border border-white/10 bg-white/3 p-6">
            <div className="flex flex-col gap-2">
                <p className="text-lg font-semibold text-white">{title}</p>
                {description ? <p className="text-sm leading-6 text-white/60">{description}</p> : null}
            </div>
            <div className="mt-5">{children}</div>
        </section>
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
        <section className="rounded-[28px] border border-cyan-400/15 bg-cyan-400/5 p-4">
            <div className="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p className="text-sm font-semibold text-white">Idiomas</p>
                    <p className="mt-1 text-xs leading-6 text-white/55">
                        Cambia el locale activo y actualiza en el mismo panel la metadata SEO correspondiente.
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

function EmptySeoState({ capabilities }) {
    return (
        <section className="rounded-[28px] border border-white/10 bg-white/3 px-6 py-8 text-center">
            <h2 className="text-lg font-semibold text-white">Todavia no hay un recurso SEO cargado</h2>
            <p className="mt-3 text-sm leading-6 text-white/60">
                Cuando exista al menos un registro SEO para una entidad, esta pantalla lo abrira directamente como editor multilingue por recurso.
            </p>
            {capabilities.canCreate ? (
                <div className="mt-5">
                    <Button href="/backoffice/seo-metas/create" variant="primary">
                        Nuevo SEO meta
                    </Button>
                </div>
            ) : null}
        </section>
    )
}

export default function SeoMetaIndexScreen({
    title,
    description,
    breadcrumbs,
    actions,
    summaryCards,
    form,
    capabilities,
}) {
    const inertiaForm = useForm(form?.defaults ?? {})

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
            headerContent={<LocaleActionsBar actions={form?.localeActions ?? []} />}
        >
            <div className="space-y-6">
                {!capabilities.canEdit ? (
                    <section className="rounded-[28px] border border-amber-400/20 bg-amber-400/10 px-5 py-4 text-sm text-amber-100">
                        Tu rol actual es de solo lectura. Puedes revisar el SEO por idioma, pero no guardar cambios.
                    </section>
                ) : null}

                {!form?.sections?.length ? (
                    <EmptySeoState capabilities={capabilities} />
                ) : (
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

                        {!form.hideSubmit ? (
                            <section className="flex flex-wrap items-center justify-end gap-3">
                                <Button type="submit" variant="primary" disabled={!capabilities.canSubmit || inertiaForm.processing}>
                                    {inertiaForm.processing ? 'Guardando...' : form.submitLabel ?? 'Guardar'}
                                </Button>
                            </section>
                        ) : null}
                    </form>
                )}
            </div>
        </BackofficeLayout>
    )
}
