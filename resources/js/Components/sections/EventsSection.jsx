import { motion } from 'framer-motion'

export default function EventsSection({ content, events }) {
    return (
        <section id="events" className="relative min-h-screen snap-start bg-neutral-950 px-6 py-20 text-white">
            <div className="mx-auto max-w-6xl">
                <h2 className="text-center text-4xl font-bold md:text-6xl">{content.title}</h2>
                {events.length === 0 ? (
                    <p className="mt-10 text-center text-white/70">{content.empty}</p>
                ) : (
                    <div className="mt-12 grid gap-4">
                        {events.map((event) => (
                            <motion.article
                                key={event.slug}
                                initial={{ opacity: 0, y: 16 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true }}
                                transition={{ duration: 0.45 }}
                                className="rounded-2xl border border-white/10 bg-white/5 p-5 md:p-6"
                            >
                                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <h3 className="text-xl font-semibold">{event.title}</h3>
                                        <p className="mt-1 text-sm text-white/65">
                                            {event.location} · {event.start_label}
                                        </p>
                                    </div>
                                    {event.external_url ? (
                                        <a
                                            href={event.external_url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="inline-flex rounded-full bg-red-600 px-4 py-2 text-sm font-semibold transition hover:bg-red-500"
                                        >
                                            {content.buy}
                                        </a>
                                    ) : null}
                                </div>
                            </motion.article>
                        ))}
                    </div>
                )}
            </div>
        </section>
    )
}
