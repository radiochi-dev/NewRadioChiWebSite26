import { Head, usePage } from '@inertiajs/react'
import { useMemo, useState } from 'react'
import BackofficeBreadcrumbs from '../Components/Backoffice/BackofficeBreadcrumbs'
import BackofficeFlashMessages from '../Components/Backoffice/BackofficeFlashMessages'
import BackofficeSidebar from '../Components/Backoffice/BackofficeSidebar'
import BackofficeTopbar from '../Components/Backoffice/BackofficeTopbar'
import Button from '../Components/Backoffice/ui/Button'

function SummaryCard({ card }) {
    const tones = {
        amber: 'from-amber-400/20 to-transparent text-amber-100',
        cyan: 'from-cyan-400/20 to-transparent text-cyan-100',
        emerald: 'from-emerald-400/20 to-transparent text-emerald-100',
        fuchsia: 'from-fuchsia-400/20 to-transparent text-fuchsia-100',
    }

    return (
        <article className={`rounded-[28px] border border-white/10 bg-gradient-to-br p-5 ${tones[card.tone] ?? tones.cyan}`}>
            <p className="text-xs font-semibold uppercase tracking-[0.24em] text-white/45">{card.label}</p>
            <p className="mt-4 text-3xl font-semibold text-white">{card.value}</p>
        </article>
    )
}

export default function BackofficeLayout({
    title,
    description,
    breadcrumbs = [],
    actions = [],
    summaryCards = [],
    children,
}) {
    const { props } = usePage()
    const [isSidebarOpen, setIsSidebarOpen] = useState(false)

    const visibleActions = useMemo(() => actions.filter((action) => action.visible !== false), [actions])

    return (
        <>
            <Head title={title}>
                <link rel="stylesheet" href="/assets/fonts/Inter/style.css" />
            </Head>

            <div className="relative h-full overflow-hidden bg-slate-950 text-white">
                <div className="pointer-events-none absolute inset-0">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(34,211,238,0.14),transparent_32%),radial-gradient(circle_at_top_right,rgba(217,70,239,0.16),transparent_30%),radial-gradient(circle_at_bottom_center,rgba(56,189,248,0.08),transparent_34%)]" />
                    <div className="absolute inset-0 opacity-[0.08]" style={{ backgroundImage: "url('/assets/img/bg/bg-texture-2.png')" }} />
                </div>

                <div className="relative flex h-full min-h-0">
                    <BackofficeSidebar
                        navigation={props.backoffice?.navigation ?? []}
                        isOpen={isSidebarOpen}
                        onClose={() => setIsSidebarOpen(false)}
                    />

                    <div className="flex min-h-0 min-w-0 flex-1 flex-col">
                        <BackofficeTopbar
                            branding={props.backoffice?.branding}
                            auth={props.auth}
                            title={title}
                            onMenuClick={() => setIsSidebarOpen(true)}
                        />

                        <main className="min-h-0 flex-1 overflow-y-auto px-4 py-6 md:px-6 lg:px-8">
                            <div className="mx-auto flex w-full max-w-[1440px] flex-col gap-6">
                                <BackofficeFlashMessages flash={props.flash} />
                                <BackofficeBreadcrumbs items={breadcrumbs} />

                                <section className="flex flex-col gap-5 rounded-[32px] border border-white/10 bg-black/20 p-6 shadow-[0_25px_80px_rgba(0,0,0,0.35)]">
                                    <div className="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                        <div className="max-w-3xl">
                                            <p className="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-300/75">
                                                RadioChi Admin Next
                                            </p>
                                            <h1 className="mt-3 text-3xl font-semibold tracking-tight text-white md:text-4xl">{title}</h1>
                                            {description ? <p className="mt-3 text-base leading-7 text-white/60">{description}</p> : null}
                                        </div>

                                        {visibleActions.length ? (
                                            <div className="flex flex-wrap items-center gap-3 xl:justify-end">
                                                {visibleActions.map((action) => (
                                                    <Button
                                                        key={`${action.label}-${action.href ?? 'button'}`}
                                                        href={action.href}
                                                        variant={action.variant}
                                                        disabled={action.disabled}
                                                        external={action.external}
                                                    >
                                                        {action.label}
                                                    </Button>
                                                ))}
                                            </div>
                                        ) : null}
                                    </div>

                                    {summaryCards.length ? (
                                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                            {summaryCards.map((card) => (
                                                <SummaryCard key={`${card.label}-${card.value}`} card={card} />
                                            ))}
                                        </div>
                                    ) : null}
                                </section>

                                <section className="min-h-0">{children}</section>
                            </div>
                        </main>
                    </div>
                </div>
            </div>
        </>
    )
}
