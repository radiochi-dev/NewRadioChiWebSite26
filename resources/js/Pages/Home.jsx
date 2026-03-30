import { useEffect, useMemo, useRef, useState } from 'react'
import PublicLayout from '../Layouts/PublicLayout'
import LegacyHeader from '../Components/legacy/LegacyHeader'
import LegacyIntro from '../Components/legacy/LegacyIntro'
import LegacyStarshine from '../Components/legacy/LegacyStarshine'
import { getLegacyCalendarData, getLegacyContent, getLegacyMediaData } from '../legacy/content'
import { FaFacebookF, FaInstagram, FaSoundcloud, FaYoutube } from 'react-icons/fa'

const sectionIds = ['home', 'about', 'music', 'calendar', 'media', 'contact']

export default function Home({ locale, locales, currentPath, events, seo }) {
    const legacy = useMemo(() => getLegacyContent(locale), [locale])
    const calendarData = useMemo(() => getLegacyCalendarData(locale), [locale])
    const mediaData = useMemo(() => getLegacyMediaData(), [])
    const [activeSectionIndex, setActiveSectionIndex] = useState(0)
    const [isScrolling, setIsScrolling] = useState(false)
    const [currentSlide, setCurrentSlide] = useState(0)
    const [currentTrack, setCurrentTrack] = useState(0)
    const [currentPhotoIndex, setCurrentPhotoIndex] = useState(0)
    const [currentVideoIndex, setCurrentVideoIndex] = useState(0)
    const [aboutStep, setAboutStep] = useState(0)
    const [aboutBgStep, setAboutBgStep] = useState(0)
    const [typedAboutTitle, setTypedAboutTitle] = useState('')
    const [lightbox, setLightbox] = useState(null)
    const [isPlaying, setIsPlaying] = useState(true)
    const [isMuted, setIsMuted] = useState(false)
    const [mediaItemsPerView, setMediaItemsPerView] = useState(5)
    const [lightboxTouchStartX, setLightboxTouchStartX] = useState(0)
    const [playerDuration, setPlayerDuration] = useState(0)
    const [playerPosition, setPlayerPosition] = useState(0)
    const [widgetReady, setWidgetReady] = useState(false)
    const [musicItemsPerView, setMusicItemsPerView] = useState(3)
    const [musicTouchStartX, setMusicTouchStartX] = useState(0)
    const [policyModal, setPolicyModal] = useState(null)
    const soundcloudRef = useRef(null)
    const soundcloudWidgetRef = useRef(null)
    const positionIntervalRef = useRef(null)
    const slides = legacy.home?.slides ?? []
    const aboutSteps = legacy.about?.scrollytelling?.steps ?? []
    const tracks = legacy.music?.tracks ?? []
    const photos = mediaData.photos ?? []
    const videos = mediaData.videos ?? []
    const sortedEvents = useMemo(
        () =>
            [...(calendarData.events ?? [])].sort((a, b) => {
                const aa = new Date(a.dateStart ?? a.date ?? '2100-01-01').getTime()
                const bb = new Date(b.dateStart ?? b.date ?? '2100-01-01').getTime()
                return aa - bb
            }),
        [calendarData.events],
    )
    const sponsorLogos = [
        { name: 'Parrots Group', url: 'https://www.parrots-sitges.com/', imgSrc: '/assets/img/logos/parrots-group.png' },
        { name: 'Sitges Pride', url: 'https://sitgespride.com/', imgSrc: '/assets/img/logos/Sitges-Pride-Logo-2025-BLUE.png' },
        { name: 'Bears Week Sitges', url: 'https://bearssitges.org/bears-sitges-week/', imgSrc: '/assets/img/logos/LOGO-BEARS-WEEK-mini.png' },
        { name: 'IBC Palm Springs', url: 'https://www.ibc-ps.com/', imgSrc: '/assets/img/logos/ibc+blue+logo+mk.webp' },
        { name: 'Bears Events', url: 'https://www.bearsevents.com/', imgSrc: '/assets/img/logos/Bear-Events-Logo-2-300x95.png' },
    ]
    const activeTrack = tracks[currentTrack]
    const policyContent = legacy.termsPolicyCookies ?? {}
    const openLegalModal = (type) => {
        if (type === 'terms') {
            setPolicyModal({ title: policyContent.terms?.title, content: policyContent.terms?.content })
            return
        }
        if (type === 'privacy') {
            setPolicyModal({ title: policyContent.privacy?.title, content: policyContent.privacy?.content })
            return
        }
        if (type === 'cookies') {
            setPolicyModal({ title: policyContent.cookies?.title, content: policyContent.cookies?.content })
        }
    }

    useEffect(() => {
        if (slides.length <= 1) {
            return
        }
        const interval = setInterval(() => {
            setCurrentSlide((prev) => (prev + 1) % slides.length)
        }, 7000)
        return () => clearInterval(interval)
    }, [slides.length])

    useEffect(() => {
        const onKeyDown = (e) => {
            if (!lightbox) {
                return
            }
            if (e.key === 'Escape') {
                setLightbox(null)
            }
            if (lightbox.type === 'image' && e.key === 'ArrowLeft') {
                setLightbox((prev) => {
                    if (!prev) return prev
                    return { ...prev, index: Math.max(0, prev.index - 1), src: photos[Math.max(0, prev.index - 1)].src, title: photos[Math.max(0, prev.index - 1)].caption, subtitle: photos[Math.max(0, prev.index - 1)].date }
                })
            }
            if (lightbox.type === 'image' && e.key === 'ArrowRight') {
                setLightbox((prev) => {
                    if (!prev) return prev
                    const idx = Math.min(photos.length - 1, prev.index + 1)
                    return { ...prev, index: idx, src: photos[idx].src, title: photos[idx].caption, subtitle: photos[idx].date }
                })
            }
        }
        window.addEventListener('keydown', onKeyDown)
        return () => window.removeEventListener('keydown', onKeyDown)
    }, [lightbox, photos])

    useEffect(() => {
        const updatePerView = () => {
            const width = window.innerWidth
            if (width < 640) {
                setMediaItemsPerView(1)
                return
            }
            if (width < 768) {
                setMediaItemsPerView(2)
                return
            }
            if (width < 1024) {
                setMediaItemsPerView(3)
                return
            }
            if (width < 1280) {
                setMediaItemsPerView(4)
                return
            }
            setMediaItemsPerView(5)
        }
        updatePerView()
        window.addEventListener('resize', updatePerView)
        return () => window.removeEventListener('resize', updatePerView)
    }, [])

    useEffect(() => {
        const updateMusicPerView = () => {
            const width = window.innerWidth
            if (width < 768) {
                setMusicItemsPerView(1)
                return
            }
            if (width < 1200) {
                setMusicItemsPerView(2)
                return
            }
            setMusicItemsPerView(3)
        }
        updateMusicPerView()
        window.addEventListener('resize', updateMusicPerView)
        return () => window.removeEventListener('resize', updateMusicPerView)
    }, [])

    useEffect(() => {
        if (aboutSteps.length === 0) {
            return
        }
        setAboutStep(0)
        setAboutBgStep(0)
        const interval = setInterval(() => {
            setAboutStep((prev) => {
                const next = (prev + 1) % aboutSteps.length
                setAboutBgStep(next)
                return next
            })
        }, 6000)
        return () => {
            clearInterval(interval)
        }
    }, [aboutSteps.length])

    useEffect(() => {
        const title = aboutSteps[aboutStep]?.title ?? ''
        if (!title) {
            setTypedAboutTitle('')
            return
        }
        setTypedAboutTitle('')
        let i = 0
        const timer = setInterval(() => {
            i += 1
            setTypedAboutTitle(title.slice(0, i))
            if (i >= title.length) {
                clearInterval(timer)
            }
        }, 50)
        return () => clearInterval(timer)
    }, [aboutStep, aboutSteps])

    useEffect(() => {
        const cleanup = () => {
            if (positionIntervalRef.current) {
                clearInterval(positionIntervalRef.current)
                positionIntervalRef.current = null
            }
        }
        const bindWidget = () => {
            if (!window.SC || !soundcloudRef.current) {
                return
            }
            const widget = window.SC.Widget(soundcloudRef.current)
            soundcloudWidgetRef.current = widget
            widget.bind(window.SC.Widget.Events.READY, () => {
                setWidgetReady(true)
                widget.getDuration((duration) => setPlayerDuration(duration || 0))
                widget.setVolume(isMuted ? 0 : 100)
                if (isPlaying) {
                    widget.play()
                } else {
                    widget.pause()
                }
            })
            widget.bind(window.SC.Widget.Events.PLAY, () => setIsPlaying(true))
            widget.bind(window.SC.Widget.Events.PAUSE, () => setIsPlaying(false))
            widget.bind(window.SC.Widget.Events.FINISH, () => {
                setCurrentTrack((prev) => (prev + 1) % tracks.length)
            })
            cleanup()
            positionIntervalRef.current = window.setInterval(() => {
                widget.getPosition((position) => setPlayerPosition(position || 0))
                widget.getDuration((duration) => setPlayerDuration(duration || 0))
            }, 500)
        }

        if (window.SC && window.SC.Widget) {
            bindWidget()
        } else {
            const script = document.createElement('script')
            script.src = 'https://w.soundcloud.com/player/api.js'
            script.onload = bindWidget
            document.body.appendChild(script)
        }

        return cleanup
    }, [activeTrack?.id, tracks.length])

    useEffect(() => {
        if (!widgetReady || !soundcloudWidgetRef.current) {
            return
        }
        soundcloudWidgetRef.current.setVolume(isMuted ? 0 : 100)
    }, [isMuted, widgetReady])

    useEffect(() => {
        if (!widgetReady || !soundcloudWidgetRef.current) {
            return
        }
        if (isPlaying) {
            soundcloudWidgetRef.current.play()
        } else {
            soundcloudWidgetRef.current.pause()
        }
    }, [isPlaying, widgetReady])

    useEffect(() => {
        document.documentElement.style.overflow = 'hidden'
        document.body.style.overflow = 'hidden'
        document.body.style.margin = '0'
        let touchStartY = 0
        let wheelTimeout = null

        const goToSection = (index, animate = true) => {
            if (index < 0 || index >= sectionIds.length || isScrolling) {
                return
            }
            if (animate) {
                setIsScrolling(true)
                window.setTimeout(() => setIsScrolling(false), 700)
            }
            setActiveSectionIndex(index)
            window.history.replaceState(null, '', `#${sectionIds[index]}`)
        }

        const moveDown = () => goToSection(activeSectionIndex + 1)
        const moveUp = () => goToSection(activeSectionIndex - 1)

        const onWheel = (e) => {
            e.preventDefault()
            if (isScrolling) {
                return
            }
            if (wheelTimeout) {
                clearTimeout(wheelTimeout)
            }
            wheelTimeout = window.setTimeout(() => {
                if (e.deltaY > 0) {
                    moveDown()
                } else {
                    moveUp()
                }
            }, 10)
        }

        const onTouchStart = (e) => {
            touchStartY = e.touches[0].clientY
        }

        const onTouchEnd = (e) => {
            const touchEndY = e.changedTouches[0].clientY
            const delta = touchStartY - touchEndY
            if (Math.abs(delta) > 50) {
                if (delta > 0) {
                    moveDown()
                } else {
                    moveUp()
                }
            }
        }

        const onKeyDown = (e) => {
            if (isScrolling) {
                return
            }
            if (['ArrowDown', 'PageDown', ' '].includes(e.key)) {
                e.preventDefault()
                moveDown()
            }
            if (['ArrowUp', 'PageUp'].includes(e.key)) {
                e.preventDefault()
                moveUp()
            }
            if (e.key === 'Home') {
                e.preventDefault()
                goToSection(0)
            }
            if (e.key === 'End') {
                e.preventDefault()
                goToSection(sectionIds.length - 1)
            }
        }

        const onHashLoad = () => {
            const hash = window.location.hash.replace('#', '')
            const idx = sectionIds.indexOf(hash)
            if (idx >= 0) {
                setActiveSectionIndex(idx)
            }
        }

        onHashLoad()
        document.addEventListener('wheel', onWheel, { passive: false })
        document.addEventListener('touchstart', onTouchStart, { passive: true })
        document.addEventListener('touchend', onTouchEnd, { passive: true })
        document.addEventListener('keydown', onKeyDown)
        window.addEventListener('hashchange', onHashLoad)

        return () => {
            if (wheelTimeout) {
                clearTimeout(wheelTimeout)
            }
            document.documentElement.style.overflow = ''
            document.body.style.overflow = ''
            document.removeEventListener('wheel', onWheel)
            document.removeEventListener('touchstart', onTouchStart)
            document.removeEventListener('touchend', onTouchEnd)
            document.removeEventListener('keydown', onKeyDown)
            window.removeEventListener('hashchange', onHashLoad)
        }
    }, [activeSectionIndex, isScrolling])

    const goTo = (id) => {
        const idx = sectionIds.indexOf(id)
        if (idx >= 0) {
            setActiveSectionIndex(idx)
            window.history.replaceState(null, '', `#${id}`)
        }
    }

    const maxPhotoIndex = Math.max(0, photos.length - mediaItemsPerView)
    const maxVideoIndex = Math.max(0, videos.length - mediaItemsPerView)
    const maxMusicOffset = Math.max(0, tracks.length - musicItemsPerView)
    const musicOffset = Math.min(maxMusicOffset, Math.max(0, currentTrack - Math.floor(musicItemsPerView / 2)))

    const contactWordsA = ['TechHouse', 'House', 'Hits', 'Contact', 'BearWeek', 'SitgesPride', 'Contacto', 'Contatto', 'Kontakt', 'Contacte']
    const contactWordsB = ['Electronic', 'Dance', 'Music', 'Festival', 'Party', 'Contacter', 'Kontakti', 'Liên-hệ', 'Επαφή', 'Контакт']
    const contactWordsC = ['RadioChi', 'Beats', 'Vibes', 'Sound', 'Waves', 'संपर्क', 'اتصال', 'Yhteystiedot', 'Kapcsolat', 'Kontak']

    useEffect(() => {
        if (!policyModal) {
            document.body.style.overflow = ''
            document.body.style.position = ''
            document.body.style.width = ''
            return
        }
        document.body.style.overflow = 'hidden'
        document.body.style.position = 'fixed'
        document.body.style.width = '100%'
        const onEsc = (e) => {
            if (e.key === 'Escape') {
                setPolicyModal(null)
            }
        }
        document.addEventListener('keydown', onEsc)
        return () => {
            document.removeEventListener('keydown', onEsc)
            document.body.style.overflow = ''
            document.body.style.position = ''
            document.body.style.width = ''
        }
    }, [policyModal])

    return (
        <PublicLayout title={legacy.home?.name ?? 'RadioChi'} locale={locale} locales={locales} currentPath={currentPath} menu={legacy.header?.menu ?? {}} seo={seo} hideNav>
            <LegacyIntro
                locale={locale}
                intro={legacy.intro}
                footer={legacy.footer}
                onTogglePlay={() => setIsPlaying((v) => !v)}
                onToggleMute={() => setIsMuted((v) => !v)}
                isMuted={isMuted}
            />

            <LegacyHeader
                locale={locale}
                locales={locales}
                currentPath={currentPath}
                menu={legacy.header?.menu}
                isPlaying={isPlaying}
                isMuted={isMuted}
                onTogglePlay={() => setIsPlaying((v) => !v)}
                onToggleMute={() => setIsMuted((v) => !v)}
                onGoTo={goTo}
                onOpenLegalModal={openLegalModal}
            />

            <div className="fullpage-container">
                <div className="fullpage-wrapper">
                    <section id="home" className={`legacy-section ${activeSectionIndex === 0 ? 'is-active' : ''}`} style={{ transform: `translateY(${activeSectionIndex > 0 ? -100 * activeSectionIndex : 0}vh)` }}>
                        <div className="legacy-animated-bg" />
                        <div className="legacy-starshine-layer">
                            <LegacyStarshine />
                        </div>
                        <div className="absolute inset-0 bg-black" />
                        {slides.map((slide, idx) => (
                            <div key={`${slide.title}-${idx}`} className={`legacy-home-slide ${idx === currentSlide ? 'active' : ''}`}>
                                <div className="legacy-home-content">
                                    <div className="legacy-home-heading">
                                        <img src={slide.logo} alt={slide.title} className="legacy-home-logo" />
                                        <h1>{slide.title}</h1>
                                    </div>
                                    <h2>{slide.subtitle}</h2>
                                    <p>{slide.description}</p>
                                    {slide.buttonText && slide.link && (
                                        <a href={slide.link} target="_blank" rel="noreferrer" className="legacy-btn">
                                            {slide.buttonText}
                                        </a>
                                    )}
                                </div>
                                <div className="legacy-home-image">
                                    <img src={slide.personImage} alt={slide.title} />
                                </div>
                                <div className="legacy-home-elipse" />
                            </div>
                        ))}
                        <div className="legacy-home-pagination">
                            {slides.map((slide, idx) => (
                                <button key={`bullet-${slide.title}-${idx}`} className={idx === currentSlide ? 'active' : ''} onClick={() => setCurrentSlide(idx)} />
                            ))}
                        </div>
                    </section>

                    <section id="about" className={`legacy-section ${activeSectionIndex === 1 ? 'is-active' : ''}`} style={{ transform: `translateY(${activeSectionIndex > 1 ? -100 * (activeSectionIndex - 1) : 0}vh)` }}>
                        {aboutSteps.map((step, idx) => (
                            <div key={`bg-${step.id}`} className={`legacy-about-bg ${idx === aboutBgStep ? 'active' : ''}`} style={{ backgroundImage: `url(${step.image})` }} />
                        ))}
                        {aboutSteps[aboutStep] && (
                            <div className="legacy-about-content">
                                <h1>{typedAboutTitle}</h1>
                                <h2>{aboutSteps[aboutStep].subtitle}</h2>
                                <p>{aboutSteps[aboutStep].content}</p>
                            </div>
                        )}
                    </section>

                    <section id="music" className={`legacy-section ${activeSectionIndex === 2 ? 'is-active' : ''}`} style={{ transform: `translateY(${activeSectionIndex > 2 ? -100 * (activeSectionIndex - 2) : 0}vh)` }}>
                        <div className="legacy-animated-bg" />
                        {activeTrack && (
                            <>
                                <div className="legacy-music-image" style={{ backgroundImage: `url(${activeTrack.image})` }} />
                                <div className="legacy-music-content">
                                    <h1>{activeTrack.heroTitle}</h1>
                                    <h2>{activeTrack.subtitle}</h2>
                                    <p>{activeTrack.description}</p>
                                    <div className="legacy-soundcloud-wrap">
                                        <div className="legacy-player-header">
                                            <div>
                                                <h3>{activeTrack.title}</h3>
                                                <p>{activeTrack.subtitle}</p>
                                            </div>
                                            <div className="legacy-player-controls">
                                                <button onClick={() => setCurrentTrack((prev) => (prev > 0 ? prev - 1 : tracks.length - 1))}>⏮</button>
                                                <button onClick={() => setIsPlaying((v) => !v)}>{isPlaying ? '⏸' : '▶'}</button>
                                                <button onClick={() => setCurrentTrack((prev) => (prev < tracks.length - 1 ? prev + 1 : 0))}>⏭</button>
                                                <button onClick={() => setIsMuted((v) => !v)}>{isMuted ? '🔇' : '🔊'}</button>
                                            </div>
                                        </div>
                                        <div className="legacy-player-progress" onClick={(e) => {
                                            if (!soundcloudWidgetRef.current || playerDuration <= 0) return
                                            const rect = e.currentTarget.getBoundingClientRect()
                                            const ratio = (e.clientX - rect.left) / rect.width
                                            const seekTo = Math.max(0, Math.min(playerDuration, ratio * playerDuration))
                                            soundcloudWidgetRef.current.seekTo(seekTo)
                                            setPlayerPosition(seekTo)
                                        }}>
                                            <div style={{ width: `${playerDuration > 0 ? (playerPosition / playerDuration) * 100 : 0}%` }} />
                                        </div>
                                        <iframe
                                            ref={soundcloudRef}
                                            key={activeTrack.id}
                                            title={activeTrack.title}
                                            src={activeTrack.soundcloudUrl}
                                            className="legacy-soundcloud"
                                            allow="autoplay"
                                        />
                                    </div>
                                    <div className="legacy-track-carousel-wrap">
                                        <button className="legacy-carousel-btn left" onClick={() => setCurrentTrack((prev) => Math.max(0, prev - 1))}>‹</button>
                                        <div
                                            className="legacy-track-carousel"
                                            onTouchStart={(e) => setMusicTouchStartX(e.touches[0].clientX)}
                                            onTouchEnd={(e) => {
                                                const delta = musicTouchStartX - e.changedTouches[0].clientX
                                                if (Math.abs(delta) < 50) return
                                                if (delta > 0) {
                                                    setCurrentTrack((prev) => Math.min(tracks.length - 1, prev + 1))
                                                } else {
                                                    setCurrentTrack((prev) => Math.max(0, prev - 1))
                                                }
                                            }}
                                        >
                                            <div className="legacy-track-cards" style={{ transform: `translateX(-${musicOffset * (100 / musicItemsPerView)}%)` }}>
                                                {tracks.map((track, idx) => (
                                                    <button key={track.id} className={`legacy-track-card ${idx === currentTrack ? 'active' : ''}`} style={{ flex: `0 0 calc(${100 / musicItemsPerView}% - 10px)` }} onClick={() => setCurrentTrack(idx)}>
                                                        <img src={track['label-img']} alt={track.title} />
                                                        <div>
                                                            <h4>{track.title}</h4>
                                                            <p>{track.subtitle}</p>
                                                        </div>
                                                    </button>
                                                ))}
                                            </div>
                                        </div>
                                        <button className="legacy-carousel-btn right" onClick={() => setCurrentTrack((prev) => Math.min(tracks.length - 1, prev + 1))}>›</button>
                                    </div>
                                    <div className="legacy-track-links">
                                        <a href={activeTrack.soundcloudUrl} target="_blank" rel="noreferrer" className="legacy-btn">
                                            SoundCloud
                                        </a>
                                        <a href="https://soundcloud.com/mrchi1" target="_blank" rel="noreferrer" className="legacy-btn">
                                            Follow
                                        </a>
                                    </div>
                                </div>
                            </>
                        )}
                    </section>

                    <section id="calendar" className={`legacy-section ${activeSectionIndex === 3 ? 'is-active' : ''}`} style={{ transform: `translateY(${activeSectionIndex > 3 ? -100 * (activeSectionIndex - 3) : 0}vh)` }}>
                        <div className="legacy-animated-bg" />
                        <div className="legacy-events-bg" />
                        <div className="legacy-events-content">
                            <h2>{calendarData.translations?.title ?? legacy.header?.menu?.calendarEvents}</h2>
                            <div className="legacy-events-list">
                                {sortedEvents.map((event) => (
                                    <article key={event.id} className="legacy-event-card">
                                        <img src={event.logo} alt={event.title} />
                                        <div>
                                            <h3>{event.title}</h3>
                                            <p>{event.dateStart || event.dateEnd ? `${event.dateStart} - ${event.dateEnd}` : calendarData.translations?.datesComingSoon}</p>
                                            <p>{event.location}, {calendarData.translations?.country?.[event.country] ?? event.country}</p>
                                        </div>
                                        <a href={event.linkEvent} target="_blank" rel="noreferrer" className="legacy-btn small">
                                            {calendarData.translations?.buyTickets ?? 'Buy Tickets'}
                                        </a>
                                    </article>
                                ))}
                            </div>
                        </div>
                    </section>

                    <section id="media" className={`legacy-section ${activeSectionIndex === 4 ? 'is-active' : ''}`} style={{ transform: `translateY(${activeSectionIndex > 4 ? -100 * (activeSectionIndex - 4) : 0}vh)` }}>
                        <div className="legacy-animated-bg" />
                        <div className="legacy-media-content">
                            <h2>{legacy.media?.title ?? 'MEDIA'}</h2>
                            <div className="legacy-media-carousel-nav">
                                <button className="legacy-nav-btn" onClick={() => setCurrentPhotoIndex((v) => Math.max(0, v - 1))}>‹</button>
                                <button className="legacy-nav-btn" onClick={() => setCurrentPhotoIndex((v) => Math.min(maxPhotoIndex, v + 1))}>›</button>
                            </div>
                            <div className="legacy-media-grid" style={{ transform: `translateX(-${currentPhotoIndex * (100 / mediaItemsPerView)}%)` }}>
                                {photos.map((photo, idx) => (
                                    <button key={photo.src} className="legacy-media-photo" style={{ flex: `0 0 calc(${100 / mediaItemsPerView}% - 8px)` }} onClick={() => setLightbox({ type: 'image', index: idx, src: photo.src, title: photo.caption, subtitle: photo.date })}>
                                        <img src={photo.src} alt={photo.alt} />
                                    </button>
                                ))}
                            </div>
                            <div className="legacy-media-carousel-nav">
                                <button className="legacy-nav-btn" onClick={() => setCurrentVideoIndex((v) => Math.max(0, v - 1))}>‹</button>
                                <button className="legacy-nav-btn" onClick={() => setCurrentVideoIndex((v) => Math.min(maxVideoIndex, v + 1))}>›</button>
                            </div>
                            <div className="legacy-media-videos">
                                {videos.slice(currentVideoIndex, currentVideoIndex + mediaItemsPerView).map((video) => (
                                    <button key={video.id} className="legacy-media-video" style={{ flex: `0 0 calc(${100 / mediaItemsPerView}% - 8px)` }} onClick={() => setLightbox({ type: 'video', src: `https://www.youtube.com/embed/${video.id}?autoplay=1`, title: video.title, subtitle: video.duration, link: `https://www.youtube.com/watch?v=${video.id}` })}>
                                        <img src={video.thumbnail} alt={video.title} />
                                        <span>{video.title}</span>
                                    </button>
                                ))}
                            </div>
                        </div>
                    </section>

                    <section id="contact" className={`legacy-section ${activeSectionIndex === 5 ? 'is-active' : ''}`} style={{ transform: `translateY(${activeSectionIndex > 5 ? -100 * (activeSectionIndex - 5) : 0}vh)` }}>
                        <div className="legacy-animated-bg" />
                        <div className="legacy-contact-bg" />
                        <div className="legacy-contact-content">
                            <h2>{legacy.contact?.title ?? 'CONTACT'}</h2>
                            <div className="legacy-social">
                                <a href="https://www.facebook.com/fernandocardonatoro" target="_blank" rel="noreferrer" aria-label="Facebook">
                                    <FaFacebookF />
                                </a>
                                <a href="https://www.instagram.com/mrchiloveyou/" target="_blank" rel="noreferrer" aria-label="Instagram">
                                    <FaInstagram />
                                </a>
                                <a href="https://soundcloud.com/mrchi1" target="_blank" rel="noreferrer" aria-label="SoundCloud">
                                    <FaSoundcloud />
                                </a>
                                <a href="https://www.youtube.com/@cardonatoro" target="_blank" rel="noreferrer" aria-label="YouTube">
                                    <FaYoutube />
                                </a>
                            </div>
                            <div className="legacy-sponsors">
                                {[...sponsorLogos, ...sponsorLogos].map((logo, idx) => (
                                    <a key={`${logo.name}-${idx}`} href={logo.url} target="_blank" rel="noreferrer">
                                        <img src={logo.imgSrc} alt={logo.name} />
                                    </a>
                                ))}
                            </div>
                            <div className="legacy-contact-marquee">
                                <div className="legacy-marquee a">{[...contactWordsA, ...contactWordsA, ...contactWordsA].map((word, idx) => <span key={`a-${idx}`}>{word}</span>)}</div>
                                <div className="legacy-marquee b">{[...contactWordsB, ...contactWordsB, ...contactWordsB].map((word, idx) => <span key={`b-${idx}`}>{word}</span>)}</div>
                                <div className="legacy-marquee c">{[...contactWordsC, ...contactWordsC, ...contactWordsC].map((word, idx) => <span key={`c-${idx}`}>{word}</span>)}</div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <footer className="pointer-events-auto fixed bottom-0 left-0 right-0 z-40 hidden h-6 items-center px-2 text-white lg:grid lg:grid-cols-[220px_1fr_120px]">
                <div className="pointer-events-none absolute inset-0 bg-transparent" />
                <div className="relative flex flex-col leading-[1]">
                    <span className="text-[9px] font-bold">{legacy.footer?.copyright ?? '© 2025 Copyright.'}</span>
                    <span className="text-[10px] font-bold">{legacy.footer?.rights ?? 'Domo Digital Studio'}</span>
                </div>
                <div className="relative flex items-center justify-center gap-4">
                    <button onClick={() => setPolicyModal({ title: policyContent.terms?.title, content: policyContent.terms?.content })}>
                        <span className="text-[10px] font-medium transition-colors duration-150 hover:text-gray-300">{policyContent.terms_button ?? 'Términos y Condiciones'}</span>
                    </button>
                    <button onClick={() => setPolicyModal({ title: policyContent.privacy?.title, content: policyContent.privacy?.content })}>
                        <span className="text-[10px] font-medium transition-colors duration-150 hover:text-gray-300">{policyContent.privacy_button ?? 'Política de Privacidad'}</span>
                    </button>
                    <button onClick={() => setPolicyModal({ title: policyContent.cookies?.title, content: policyContent.cookies?.content })}>
                        <span className="text-[10px] font-medium transition-colors duration-150 hover:text-gray-300">{policyContent.cookies_button ?? 'Política de Cookies'}</span>
                    </button>
                </div>
                <div className="relative flex items-center justify-end gap-1.5 text-[10px]">
                    <a className="opacity-95 transition-opacity duration-150 hover:opacity-70" href="https://www.facebook.com/fernandocardonatoro" target="_blank" rel="noreferrer" aria-label="Facebook"><FaFacebookF /></a>
                    <a className="opacity-95 transition-opacity duration-150 hover:opacity-70" href="https://www.instagram.com/mrchiloveyou/" target="_blank" rel="noreferrer" aria-label="Instagram"><FaInstagram /></a>
                    <a className="opacity-95 transition-opacity duration-150 hover:opacity-70" href="https://soundcloud.com/mrchi1" target="_blank" rel="noreferrer" aria-label="SoundCloud"><FaSoundcloud /></a>
                    <a className="opacity-95 transition-opacity duration-150 hover:opacity-70" href="https://www.youtube.com/@cardonatoro" target="_blank" rel="noreferrer" aria-label="YouTube"><FaYoutube /></a>
                </div>
            </footer>

            {activeSectionIndex < sectionIds.length - 1 && (
                <button className="legacy-scroll-indicator" onClick={() => goTo(sectionIds[Math.min(sectionIds.length - 1, activeSectionIndex + 1)])}>
                    <span className="legacy-scroll-dot" />
                </button>
            )}

            <div className="legacy-progress">
                <div className="legacy-progress-bg" />
                <div className="legacy-progress-active" style={{ height: `${((activeSectionIndex + 1) / sectionIds.length) * 100}%` }} />
                {sectionIds.map((id, idx) => (
                    <button key={id} className={idx === activeSectionIndex ? 'active' : ''} style={{ top: `${(idx / (sectionIds.length - 1)) * 100}%` }} onClick={() => goTo(id)} />
                ))}
            </div>

            {lightbox && (
                <div className="legacy-lightbox" onClick={() => setLightbox(null)}>
                    <div className="legacy-lightbox-content" onClick={(e) => e.stopPropagation()}>
                        <button className="legacy-lightbox-close" onClick={() => setLightbox(null)}>×</button>
                        <div
                            className="legacy-lightbox-media"
                            onTouchStart={(e) => setLightboxTouchStartX(e.touches[0].clientX)}
                            onTouchEnd={(e) => {
                                if (lightbox.type !== 'image') {
                                    return
                                }
                                const delta = lightboxTouchStartX - e.changedTouches[0].clientX
                                if (Math.abs(delta) < 50) {
                                    return
                                }
                                if (delta > 0) {
                                    setLightbox((prev) => {
                                        const idx = Math.min(photos.length - 1, (prev?.index ?? 0) + 1)
                                        return { ...prev, index: idx, src: photos[idx].src, title: photos[idx].caption, subtitle: photos[idx].date }
                                    })
                                } else {
                                    setLightbox((prev) => {
                                        const idx = Math.max(0, (prev?.index ?? 0) - 1)
                                        return { ...prev, index: idx, src: photos[idx].src, title: photos[idx].caption, subtitle: photos[idx].date }
                                    })
                                }
                            }}
                        >
                            {lightbox.type === 'video' ? (
                                <iframe src={lightbox.src} title={lightbox.title} allow="autoplay; encrypted-media; picture-in-picture" allowFullScreen />
                            ) : (
                                <img src={lightbox.src} alt={lightbox.title} />
                            )}
                        </div>
                        <div className="legacy-lightbox-footer">
                            <p>{lightbox.title}</p>
                            <span>{lightbox.subtitle}</span>
                            {lightbox.type === 'image' && (
                                <div className="legacy-lightbox-arrows">
                                    <button onClick={() => setLightbox((prev) => {
                                        const idx = Math.max(0, (prev?.index ?? 0) - 1)
                                        return { ...prev, index: idx, src: photos[idx].src, title: photos[idx].caption, subtitle: photos[idx].date }
                                    })}>‹</button>
                                    <button onClick={() => setLightbox((prev) => {
                                        const idx = Math.min(photos.length - 1, (prev?.index ?? 0) + 1)
                                        return { ...prev, index: idx, src: photos[idx].src, title: photos[idx].caption, subtitle: photos[idx].date }
                                    })}>›</button>
                                </div>
                            )}
                            {lightbox.link && (
                                <a href={lightbox.link} target="_blank" rel="noreferrer">Ver en YouTube</a>
                            )}
                        </div>
                    </div>
                </div>
            )}

            {policyModal && (
                <div className="legacy-policy-modal" onClick={() => setPolicyModal(null)}>
                    <div className="legacy-policy-card" onClick={(e) => e.stopPropagation()}>
                        <button className="legacy-policy-close" onClick={() => setPolicyModal(null)}>×</button>
                        <div className="legacy-policy-head">
                            <h3>{policyModal.title}</h3>
                        </div>
                        <div className="legacy-policy-body" dangerouslySetInnerHTML={{ __html: policyModal.content ?? '' }} />
                    </div>
                </div>
            )}

            <main className="sr-only">
                {events.length > 0 && events[0].title}
            </main>
        </PublicLayout>
    )
}
