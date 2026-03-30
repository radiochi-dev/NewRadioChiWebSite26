import { motion } from 'framer-motion'

export default function AboutSection({ content }) {
    return (
        <section id="about" className="relative flex min-h-screen snap-start items-center bg-neutral-900 px-6 py-20 text-white">
            <div className="mx-auto grid w-full max-w-6xl gap-10 md:grid-cols-2">
                <motion.div
                    initial={{ opacity: 0, x: -40 }}
                    whileInView={{ opacity: 1, x: 0 }}
                    viewport={{ once: true }}
                    transition={{ duration: 0.6 }}
                >
                    <p className="text-sm uppercase tracking-[0.22em] text-white/50">{content.title}</p>
                    <h2 className="mt-4 text-4xl font-bold leading-tight md:text-6xl">{content.subtitle}</h2>
                </motion.div>
                <motion.p
                    initial={{ opacity: 0, x: 40 }}
                    whileInView={{ opacity: 1, x: 0 }}
                    viewport={{ once: true }}
                    transition={{ duration: 0.6 }}
                    className="self-center text-lg leading-relaxed text-white/75"
                >
                    {content.content}
                </motion.p>
            </div>
        </section>
    )
}
