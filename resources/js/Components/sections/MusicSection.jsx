import { motion } from 'framer-motion'

export default function MusicSection({ content }) {
    return (
        <section id="music" className="relative min-h-screen snap-start bg-black px-6 py-20 text-white">
            <div className="mx-auto max-w-6xl">
                <motion.h2
                    initial={{ opacity: 0, y: 20 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ duration: 0.5 }}
                    className="text-center text-4xl font-bold md:text-6xl"
                >
                    {content.title}
                </motion.h2>
                <p className="mt-3 text-center text-white/70">{content.subtitle}</p>
                <div className="mt-12 grid gap-4 md:grid-cols-3">
                    {content.items.map((item) => (
                        <motion.div
                            key={item}
                            initial={{ opacity: 0, y: 18 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.45 }}
                            className="rounded-2xl border border-white/10 bg-white/5 p-6"
                        >
                            <h3 className="text-lg font-semibold">{item}</h3>
                            <p className="mt-2 text-sm text-white/60">Audio interactivo, integración social y sincronización con catálogo.</p>
                        </motion.div>
                    ))}
                </div>
            </div>
        </section>
    )
}
