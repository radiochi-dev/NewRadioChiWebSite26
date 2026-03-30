import { motion } from 'framer-motion'

const socials = [
    { label: 'Facebook', href: 'https://www.facebook.com/fernandocardonatoro' },
    { label: 'Instagram', href: 'https://www.instagram.com/mrchiloveyou/' },
    { label: 'YouTube', href: 'https://www.youtube.com/@cardonatoro' },
]

export default function ContactSection({ content }) {
    return (
        <section id="contact" className="relative flex min-h-screen snap-start items-center bg-black px-6 py-20 text-white">
            <div className="mx-auto w-full max-w-6xl">
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
                <div className="mt-10 flex flex-wrap items-center justify-center gap-3">
                    {socials.map((social) => (
                        <a
                            key={social.label}
                            href={social.href}
                            target="_blank"
                            rel="noreferrer"
                            className="rounded-full border border-white/20 bg-white/10 px-5 py-2 text-sm font-semibold transition hover:bg-white/20"
                        >
                            {social.label}
                        </a>
                    ))}
                </div>
            </div>
        </section>
    )
}
