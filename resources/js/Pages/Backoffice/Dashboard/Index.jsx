import BackofficeLayout from '../../../Layouts/BackofficeLayout'
import Button from '../../../Components/Backoffice/ui/Button'
import DataTable from '../../../Components/Backoffice/ui/DataTable'

function SessionChip({ children }) {
    return (
        <span className="inline-flex rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-cyan-100">
            {children}
        </span>
    )
}

function Panel({ title, description, children }) {
    return (
        <article className="rounded-[28px] border border-white/10 bg-white/[0.03] p-6">
            <p className="text-lg font-semibold text-white">{title}</p>
            {description ? <p className="mt-2 text-sm leading-6 text-white/60">{description}</p> : null}
            <div className="mt-5">{children}</div>
        </article>
    )
}

function SessionPanel({ sessionPanel }) {
    return (
        <Panel
            title="Sesion actual"
            description="Equivalente funcional del contexto que hoy resuelve `AccountWidget`, pero integrado en la superficie Inertia del nuevo backoffice."
        >
            <div className="space-y-5">
                <div>
                    <p className="text-base font-semibold text-white">{sessionPanel.user.name}</p>
                    <p className="mt-1 text-sm text-white/60">{sessionPanel.user.email}</p>
                </div>

                <div className="flex flex-wrap gap-2">
                    {sessionPanel.user.roles.length ? (
                        sessionPanel.user.roles.map((role) => <SessionChip key={role}>{role}</SessionChip>)
                    ) : (
                        <SessionChip>sin rol spatie</SessionChip>
                    )}
                </div>

                <dl className="grid gap-3 sm:grid-cols-3">
                    <div className="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <dt className="text-xs uppercase tracking-[0.2em] text-white/45">Backoffice</dt>
                        <dd className="mt-2 text-sm font-semibold text-white">{sessionPanel.access.backoffice}</dd>
                    </div>
                    <div className="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <dt className="text-xs uppercase tracking-[0.2em] text-white/45">Contenido</dt>
                        <dd className="mt-2 text-sm font-semibold text-white">{sessionPanel.access.content}</dd>
                    </div>
                    <div className="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <dt className="text-xs uppercase tracking-[0.2em] text-white/45">Superficie</dt>
                        <dd className="mt-2 text-sm font-semibold text-white">{sessionPanel.access.surface}</dd>
                    </div>
                </dl>

                {sessionPanel.legacyLinks.length ? (
                    <div className="flex flex-wrap gap-3">
                        {sessionPanel.legacyLinks.map((link) => (
                            <Button key={link.href} href={link.href} variant="ghost">
                                {link.label}
                            </Button>
                        ))}
                    </div>
                ) : null}
            </div>
        </Panel>
    )
}

function QuickActionsPanel({ quickActions }) {
    return (
        <Panel
            title="Accesos rapidos"
            description="Entradas directas a los recursos principales del CMS ya consolidadas en la superficie oficial del backoffice."
        >
            <div className="grid gap-4 xl:grid-cols-3">
                {quickActions.map((group) => (
                    <section key={group.title} className="rounded-2xl border border-white/10 bg-black/20 p-4">
                        <p className="text-sm font-semibold text-white">{group.title}</p>
                        <p className="mt-2 text-sm leading-6 text-white/60">{group.description}</p>
                        <div className="mt-4 flex flex-wrap gap-3">
                            {group.actions.map((action) => (
                                <Button key={action.href} href={action.href} variant={action.variant}>
                                    {action.label}
                                </Button>
                            ))}
                        </div>
                    </section>
                ))}
            </div>
        </Panel>
    )
}

function RecentTablePanel({ section }) {
    return (
        <Panel title={section.title} description={section.description}>
            <DataTable table={section.table} />
        </Panel>
    )
}

export default function DashboardIndex({
    title,
    description,
    breadcrumbs,
    actions,
    summaryCards,
    sessionPanel,
    quickActions,
    recentTables,
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
                <section className="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]">
                    <SessionPanel sessionPanel={sessionPanel} />
                    <Panel
                        title="Estado del corte"
                        description="El dashboard raiz y los CRUD oficiales ya son la unica superficie activa del backoffice."
                    >
                        <ul className="space-y-3 text-sm leading-6 text-white/70">
                            <li>Las metricas conservan el dominio funcional que antes cubria `BackofficeOverview`.</li>
                            <li>La sesion actual sustituye el valor practico que aportaba `AccountWidget`.</li>
                            <li>Las rutas legacy retiradas en Fase 10 ya no forman parte del runtime admin.</li>
                        </ul>
                    </Panel>
                </section>

                <QuickActionsPanel quickActions={quickActions} />

                <section className="grid gap-4 2xl:grid-cols-2">
                    {recentTables.map((section) => (
                        <RecentTablePanel key={section.title} section={section} />
                    ))}
                </section>
            </div>
        </BackofficeLayout>
    )
}
