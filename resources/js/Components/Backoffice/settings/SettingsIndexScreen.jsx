import BackofficeLayout from '../../../Layouts/BackofficeLayout'
import Button from '../ui/Button'

function LocaleActionsBar({ actions = [] }) {
    if (!actions.length) {
        return null
    }

    return (
        <section className="rounded-[28px] border border-cyan-400/15 bg-cyan-400/5 p-4">
            <div className="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p className="text-sm font-semibold text-white">Idioma de trabajo</p>
                    <p className="mt-1 text-xs leading-6 text-white/55">
                        Cambia el locale activo para abrir directamente la configuración traducible del sitio en ese idioma.
                    </p>
                </div>
                <div className="flex flex-wrap gap-2">
                    {actions.map((action) => (
                        <Button
                            key={`${action.label}-${action.href ?? 'button'}`}
                            href={action.href}
                            variant={action.variant}
                            aria-current={action.active ? 'page' : undefined}
                            className={
                                action.active
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

function SectionFact({ fact }) {
    return (
        <div className="rounded-2xl border border-white/10 bg-white/2 px-4 py-4">
            <p className="text-[11px] font-semibold uppercase tracking-[0.22em] text-white/45">{fact.label}</p>
            <p className="mt-2 text-sm font-semibold text-white">{fact.value}</p>
        </div>
    )
}

function SectionItem({ item }) {
    return (
        <div className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/2 px-4 py-4">
            <div className="min-w-0 flex-1">
                <p className="text-sm font-semibold text-white">{item.label}</p>
                {item.meta ? <p className="mt-1 text-sm leading-6 text-white/55">{item.meta}</p> : null}
            </div>
            {item.href ? (
                <Button href={item.href} variant="secondary">
                    Abrir
                </Button>
            ) : null}
        </div>
    )
}

function SectionPanel({ section }) {
    return (
        <section className="rounded-[28px] border border-white/10 bg-white/3 p-6">
            <div>
                <h2 className="text-lg font-semibold text-white">{section.title}</h2>
                {section.description ? <p className="mt-2 text-sm leading-6 text-white/60">{section.description}</p> : null}
            </div>

            {section.facts?.length ? (
                <div className="mt-5 grid gap-3 md:grid-cols-2">
                    {section.facts.map((fact) => (
                        <SectionFact key={`${section.title}-${fact.label}`} fact={fact} />
                    ))}
                </div>
            ) : null}

            {section.items?.length ? (
                <div className="mt-5 space-y-3">
                    {section.items.map((item) => (
                        <SectionItem key={item.id} item={item} />
                    ))}
                </div>
            ) : null}
        </section>
    )
}

export default function SettingsIndexScreen({
    title,
    description,
    breadcrumbs,
    actions,
    summaryCards,
    settingsDashboard,
}) {
    return (
        <BackofficeLayout
            title={title}
            description={description}
            breadcrumbs={breadcrumbs}
            actions={actions}
            summaryCards={summaryCards}
            headerContent={<LocaleActionsBar actions={settingsDashboard?.localeActions ?? []} />}
        >
            <div className="grid gap-6 xl:grid-cols-2">
                {(settingsDashboard?.sections ?? []).map((section) => (
                    <SectionPanel key={section.title} section={section} />
                ))}
            </div>
        </BackofficeLayout>
    )
}
