import { motion } from 'framer-motion'

export default function HomeSection({ content }) {
    return (
        <section id="home" className="relative flex min-h-screen snap-start items-center justify-center overflow-hidden bg-neutral-950 px-6 pt-24 text-white">
            <div className="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.14),transparent_40%),radial-gradient(circle_at_80%_70%,rgba(168,85,247,0.20),transparent_35%)]" />
            <div className="relative mx-auto max-w-5xl text-center">
                <motion.h1
                    initial={{ opacity: 0, y: 30 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ duration: 0.6 }}
                    className="text-5xl font-bold tracking-tight md:text-7xl"
                >
                    {content.title}
                </motion.h1>
                <motion.h2
                    initial={{ opacity: 0, y: 20 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ duration: 0.6, delay: 0.1 }}
                    className="mt-4 text-2xl font-semibold text-white/90 md:text-3xl"
                >
                    {content.subtitle}
                </motion.h2>
                <motion.p
                    initial={{ opacity: 0, y: 20 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ duration: 0.6, delay: 0.2 }}
                    className="mx-auto mt-6 max-w-2xl text-base text-white/70 md:text-lg"
                >
                    {content.description}
                </motion.p>
                <motion.a
                    initial={{ opacity: 0, y: 20 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ duration: 0.6, delay: 0.3 }}
                    href="#music"
                    className="mt-8 inline-flex rounded-full border border-white/25 bg-white/10 px-6 py-3 text-sm font-semibold transition hover:bg-white/20"
                >
                    {content.cta}
                </motion.a>
            </div>
        </section>
    )
}
