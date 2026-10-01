import { motion } from 'framer-motion'
import abraxas2Image from '../../../images/media/Abraxas2.webp'
import decadance1Image from '../../../images/media/Decadance1.webp'
import prideGranCanarias1Image from '../../../images/media/PrideGranCanarias1.webp'
import sitgesPride1Image from '../../../images/media/SitgesPride1.webp'

const photos = [
    {
        src: abraxas2Image,
        alt: 'Fotografia del evento Abraxas en la galeria de RadioChi',
    },
    {
        src: decadance1Image,
        alt: 'Fotografia del evento Decadance en la galeria de RadioChi',
    },
    {
        src: prideGranCanarias1Image,
        alt: 'Fotografia de Pride Gran Canaria en la galeria de RadioChi',
    },
    {
        src: sitgesPride1Image,
        alt: 'Fotografia de Sitges Pride en la galeria de RadioChi',
    },
]

const videos = [
    {
        id: '3CJOX4v3hww',
        alt: 'Miniatura del video de RadioChi en YouTube 3CJOX4v3hww',
    },
    {
        id: 'L_NqmQPf1Mw',
        alt: 'Miniatura del video de RadioChi en YouTube L_NqmQPf1Mw',
    },
    {
        id: '0dSI9i1G3dc',
        alt: 'Miniatura del video de RadioChi en YouTube 0dSI9i1G3dc',
    },
]

export default function MediaSection({ content }) {
    return (
        <section id="media" className="relative min-h-screen snap-start bg-gradient-to-br from-purple-700 to-indigo-900 px-6 py-20 text-white">
            <div className="mx-auto max-w-6xl">
                <h2 className="text-center text-4xl font-bold md:text-6xl">{content.title}</h2>
                <h3 className="mt-10 text-xl font-semibold">{content.photos}</h3>
                <div className="mt-4 grid gap-3 sm:grid-cols-2 md:grid-cols-4">
                    {photos.map((photo) => (
                        <motion.img
                            key={photo.src}
                            initial={{ opacity: 0, scale: 0.96 }}
                            whileInView={{ opacity: 1, scale: 1 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.35 }}
                            src={photo.src}
                            alt={photo.alt}
                            className="h-40 w-full rounded-xl object-cover"
                        />
                    ))}
                </div>
                <h3 className="mt-10 text-xl font-semibold">{content.videos}</h3>
                <div className="mt-4 grid gap-3 sm:grid-cols-2 md:grid-cols-3">
                    {videos.map((video) => (
                        <a
                            key={video.id}
                            href={`https://www.youtube.com/watch?v=${video.id}`}
                            target="_blank"
                            rel="noreferrer"
                            className="group overflow-hidden rounded-xl border border-white/20 bg-white/10"
                        >
                            <img src={`https://i.ytimg.com/vi/${video.id}/hqdefault.jpg`} alt={video.alt} className="h-44 w-full object-cover transition group-hover:scale-105" />
                        </a>
                    ))}
                </div>
            </div>
        </section>
    )
}
