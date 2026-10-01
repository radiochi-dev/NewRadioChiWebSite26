import { Head } from '@inertiajs/react'

export default function NewsletterStatus({ title, message, tone = 'success', primaryAction }) {
    const toneClasses = tone === 'success'
        ? 'border-emerald-400/30 bg-emerald-500/10 text-emerald-50'
        : 'border-rose-400/30 bg-rose-500/10 text-rose-50'

    const badgeClasses = tone === 'success'
        ? 'bg-emerald-400/15 text-emerald-200'
        : 'bg-rose-400/15 text-rose-200'

    return (
        <>
            <Head title={title} />
            <main className="min-h-screen bg-[#020617] px-6 py-16 text-white sm:px-10">
                <div className="mx-auto flex min-h-[calc(100vh-8rem)] max-w-3xl items-center justify-center">
                    <section className={`w-full rounded-[32px] border p-8 shadow-2xl shadow-black/30 backdrop-blur md:p-12 ${toneClasses}`}>
                        <span className={`inline-flex rounded-full px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] ${badgeClasses}`}>
                            Newsletter
                        </span>
                        <h1 className="mt-6 text-3xl font-semibold tracking-tight sm:text-4xl">{title}</h1>
                        <p className="mt-4 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">{message}</p>
                        {primaryAction?.href ? (
                            <div className="mt-8">
                                <a
                                    href={primaryAction.href}
                                    className="inline-flex items-center justify-center rounded-full border border-white/20 bg-white/10 px-6 py-3 text-sm font-medium text-white transition hover:bg-white/15"
                                >
                                    {primaryAction.label}
                                </a>
                            </div>
                        ) : null}
                    </section>
                </div>
            </main>
        </>
    )
}
