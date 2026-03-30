import { motion } from 'framer-motion'

const photos = [
    '/assets/img/media/Abraxas2.webp',
    '/assets/img/media/Decadance1.webp',
    '/assets/img/media/PrideGranCanarias1.webp',
    '/assets/img/media/SitgesPride1.webp',
]

const videos = ['3CJOX4v3hww', 'L_NqmQPf1Mw', '0dSI9i1G3dc']

export default function MediaSection({ content }) {
    return (
        <section id="media" className="relative min-h-screen snap-start bg-gradient-to-br from-purple-700 to-indigo-900 px-6 py-20 text-white">
            <div className="mx-auto max-w-6xl">
                <h2 className="text-center text-4xl font-bold md:text-6xl">{content.title}</h2>
                <h3 className="mt-10 text-xl font-semibold">{content.photos}</h3>
                <div className="mt-4 grid gap-3 sm:grid-cols-2 md:grid-cols-4">
                    {photos.map((src) => (
                        <motion.img
                            key={src}
                            initial={{ opacity: 0, scale: 0.96 }}
                            whileInView={{ opacity: 1, scale: 1 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.35 }}
                            src={src}
                            alt="RadioChi media"
                            className="h-40 w-full rounded-xl object-cover"
                        />
                    ))}
                </div>
                <h3 className="mt-10 text-xl font-semibold">{content.videos}</h3>
                <div className="mt-4 grid gap-3 sm:grid-cols-2 md:grid-cols-3">
                    {videos.map((id) => (
                        <a
                            key={id}
                            href={`https://www.youtube.com/watch?v=${id}`}
                            target="_blank"
                            rel="noreferrer"
                            className="group overflow-hidden rounded-xl border border-white/20 bg-white/10"
                        >
                            <img src={`https://i.ytimg.com/vi/${id}/hqdefault.jpg`} alt="Video preview" className="h-44 w-full object-cover transition group-hover:scale-105" />
                        </a>
                    ))}
                </div>
            </div>
        </section>
    )
}
