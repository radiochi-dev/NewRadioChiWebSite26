import { Head } from '@inertiajs/react'
import SafeHtml from '../../Components/SafeHtml'

export default function LegalShow({ title, summary, content }) {
    return (
        <>
            <Head title={title} />
            <main className="min-h-screen bg-[#020617] px-6 py-16 text-white sm:px-10">
                <div className="mx-auto max-w-4xl rounded-[32px] border border-white/10 bg-white/[0.04] p-8 shadow-2xl shadow-black/30 backdrop-blur md:p-12">
                    <h1 className="text-3xl font-semibold tracking-tight text-white sm:text-4xl">{title}</h1>
                    {summary ? (
                        <p className="mt-4 text-sm leading-7 text-white/70 sm:text-base">{summary}</p>
                    ) : null}
                    <SafeHtml
                        as="article"
                        className="newsletter-legal-content mt-8 text-sm leading-7 text-white/80 sm:text-base"
                        html={content}
                    />
                </div>
            </main>
        </>
    )
}
