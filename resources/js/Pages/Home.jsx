import { useEffect, useMemo, useRef, useState } from 'react'
import PublicLayout from '../Layouts/PublicLayout'
import LegacyHeader from '../Components/legacy/LegacyHeader'
import LegacyIntro from '../Components/legacy/LegacyIntro'
import LegacyStarshine from '../Components/legacy/LegacyStarshine'
import { AiFillInstagram } from 'react-icons/ai'
import { FaFacebookSquare, FaSpotify } from 'react-icons/fa'
import { ImSoundcloud2 } from 'react-icons/im'

const sectionIds = ['home', 'about', 'music', 'calendar', 'media', 'contact']
const shuffleArray = (items) => {
    const next = [...items]
    for (let index = next.length - 1; index > 0; index -= 1) {
        const randomIndex = Math.floor(Math.random() * (index + 1))
        ;[next[index], next[randomIndex]] = [next[randomIndex], next[index]]
    }
    return next
}
const ContactMarqueeRow = ({ rowClass, words }) => (
    <div className={`legacy-marquee-row ${rowClass}`}>
        <div className={`legacy-marquee-track ${rowClass}`}>
            {[0, 1].map((copyIndex) => (
                <h2 key={`${rowClass}-${copyIndex}`} className="legacy-marquee">
                    {words.map((word, wordIndex) => (
                        <span key={`${rowClass}-${copyIndex}-${wordIndex}`}>{word}</span>
                    ))}
                </h2>
            ))}
        </div>
    </div>
)
const MediaNavIcon = ({ direction }) => (
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
        {direction === 'prev'
            ? <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7" />
            : <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />}
    </svg>
)
const MediaCarouselRow = ({
    items,
    itemType,
    currentIndex,
    setCurrentIndex,
    maxIndex,
    perView,
    label,
    onOpen,
}) => (
    <div className={`legacy-media-row ${itemType === 'video' ? 'is-videos' : 'is-photos'}`}>
        <div className="legacy-media-carousel-shell">
            <button
                type="button"
                className="legacy-media-nav legacy-media-nav-prev"
                onClick={() => setCurrentIndex((value) => Math.max(0, value - 1))}
                disabled={currentIndex === 0}
                aria-label={itemType === 'video' ? 'Mostrar videos anteriores' : 'Mostrar fotos anteriores'}
            >
                <MediaNavIcon direction="prev" />
            </button>
            <div className="legacy-media-viewport">
                <div className="legacy-media-track" style={{ transform: `translateX(-${currentIndex * (100 / perView)}%)` }}>
                    {items.map((item, idx) => (
                        <div key={itemType === 'video' ? item.id : item.src} className="legacy-media-cell" style={{ flex: `0 0 ${100 / perView}%` }}>
                            <button
                                type="button"
                                className={`legacy-media-card ${itemType === 'video' ? 'is-video' : 'is-photo'}`}
                                onClick={() => onOpen(item, idx)}
                            >
                                <div className="legacy-media-figure">
                                    <img src={itemType === 'video' ? item.thumbnail : item.src} alt={itemType === 'video' ? item.title : item.alt} />
                                    <span className="legacy-media-badge">{label}</span>
                                    {itemType === 'video' && (
                                        <span className="legacy-media-play" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                    )}
                                    <span className="legacy-media-overlay">
                                        <span className="legacy-media-overlay-title">{itemType === 'video' ? item.title : item.caption}</span>
                                        <span className="legacy-media-overlay-subtitle">{itemType === 'video' ? item.duration : item.date}</span>
                                    </span>
                                </div>
                            </button>
                        </div>
                    ))}
                </div>
            </div>
            <button
                type="button"
                className="legacy-media-nav legacy-media-nav-next"
                onClick={() => setCurrentIndex((value) => Math.min(maxIndex, value + 1))}
                disabled={currentIndex >= maxIndex}
                aria-label={itemType === 'video' ? 'Mostrar mas videos' : 'Mostrar mas fotos'}
            >
                <MediaNavIcon direction="next" />
            </button>
        </div>
    </div>
)

export default function Home({ locale, locales, currentPath, events, seo, content, calendarData, mediaData, contactData }) {
    const legacy = content ?? {}
    const [activeSectionIndex, setActiveSectionIndex] = useState(() => {
        if (typeof window === 'undefined') {
            return 0
        }
        const initialHash = window.location.hash.replace('#', '').split('/')[0]
        const initialIndex = sectionIds.indexOf(initialHash)
        return initialIndex >= 0 ? initialIndex : 0
    })
    const [isScrolling, setIsScrolling] = useState(false)
    const [currentSlide, setCurrentSlide] = useState(0)
    const [currentTrack, setCurrentTrack] = useState(0)
    const [currentPhotoIndex, setCurrentPhotoIndex] = useState(0)
    const [currentVideoIndex, setCurrentVideoIndex] = useState(0)
    const [aboutTextIndex, setAboutTextIndex] = useState(0)
    const [aboutBgIndex, setAboutBgIndex] = useState(0)
    const [typedAboutTitle, setTypedAboutTitle] = useState('')
    const [aboutSubtitleVisible, setAboutSubtitleVisible] = useState(false)
    const [aboutDescriptionVisible, setAboutDescriptionVisible] = useState(false)
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
    const calendarScrollRef = useRef(null)
    const sectionRefs = useRef([])
    const activeSectionIndexRef = useRef(activeSectionIndex)
    const isScrollingRef = useRef(isScrolling)
    const didMountSectionsRef = useRef(false)
    const sectionAnimationTimeoutsRef = useRef([])
    const sequentialNavTimeoutsRef = useRef([])
    const aboutTimeoutsRef = useRef([])
    const aboutTextIntervalRef = useRef(null)
    const aboutBgIntervalRef = useRef(null)
    const aboutTextOrderRef = useRef([])
    const aboutBgOrderRef = useRef([])
    const aboutTextPointerRef = useRef(0)
    const aboutBgPointerRef = useRef(0)
    const slides = legacy.home?.slides ?? []
    const aboutSteps = legacy.about?.scrollytelling?.steps ?? []
    const aboutImages = useMemo(
        () => aboutSteps.flatMap((step) => [step.image, step.image2].filter(Boolean)),
        [aboutSteps],
    )
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
    const sponsorLogos = contactData?.sponsorLogos ?? []
    const contactSocialLinks = contactData?.socialLinks ?? []
    const footerSocialLinks = contactData?.footerSocialLinks ?? []
    const marqueeRows = contactData?.marqueeRows ?? []
    const activeTrack = tracks[currentTrack]
    const activeAboutStep = aboutSteps[aboutTextIndex]
    const policyContent = legacy.termsPolicyCookies ?? {}
    const findSocialLink = (links, platform) => links.find((link) => link.platform === platform) ?? { url: '#', label: platform }
    const clearSectionAnimationTimeouts = () => {
        sectionAnimationTimeoutsRef.current.forEach((timeoutId) => window.clearTimeout(timeoutId))
        sectionAnimationTimeoutsRef.current = []
    }
    const clearSequentialNavTimeouts = () => {
        sequentialNavTimeoutsRef.current.forEach((timeoutId) => window.clearTimeout(timeoutId))
        sequentialNavTimeoutsRef.current = []
    }
    const clearAboutAnimation = () => {
        aboutTimeoutsRef.current.forEach((timeoutId) => window.clearTimeout(timeoutId))
        aboutTimeoutsRef.current = []
        if (aboutTextIntervalRef.current) {
            window.clearInterval(aboutTextIntervalRef.current)
            aboutTextIntervalRef.current = null
        }
        if (aboutBgIntervalRef.current) {
            window.clearInterval(aboutBgIntervalRef.current)
            aboutBgIntervalRef.current = null
        }
    }
    const formatPlayerTime = (milliseconds) => {
        if (!milliseconds || Number.isNaN(milliseconds)) {
            return '00:00'
        }

        const totalSeconds = Math.max(0, Math.floor(milliseconds / 1000))
        const minutes = Math.floor(totalSeconds / 60)
        const seconds = totalSeconds % 60

        return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
    }
    const goPrevTrack = () => {
        setCurrentTrack((prev) => (prev > 0 ? prev - 1 : Math.max(0, tracks.length - 1)))
    }
    const goNextTrack = () => {
        setCurrentTrack((prev) => (prev < tracks.length - 1 ? prev + 1 : 0))
    }
    const openTrackLink = (url) => {
        if (!url) {
            return
        }
        window.open(url, '_blank', 'noopener,noreferrer')
    }
    const handleShareTrack = () => {
        const shareUrl = activeTrack?.soundcloudUrl || 'https://soundcloud.com/mrchi1'
        window.open(`https://wa.me/?text=${encodeURIComponent(shareUrl)}`, '_blank', 'noopener,noreferrer')
    }
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
    const openPhotoLightbox = (photo, index) => {
        setLightbox({ type: 'image', index, src: photo.src, title: photo.caption, subtitle: photo.date })
    }
    const openVideoLightbox = (video) => {
        if (soundcloudWidgetRef.current) {
            soundcloudWidgetRef.current.pause()
        }
        setLightbox({
            type: 'video',
            src: `https://www.youtube.com/embed/${video.id}?autoplay=1`,
            title: video.title,
            subtitle: video.date,
            link: `https://www.youtube.com/watch?v=${video.id}`,
        })
    }

    useEffect(() => {
        activeSectionIndexRef.current = activeSectionIndex
    }, [activeSectionIndex])

    useEffect(() => {
        isScrollingRef.current = isScrolling
    }, [isScrolling])

    useEffect(() => {
        const shouldAnimate = didMountSectionsRef.current
        clearSectionAnimationTimeouts()

        sectionRefs.current.forEach((section, index) => {
            if (!section) {
                return
            }

            const translateY = index < activeSectionIndex ? -100 * (activeSectionIndex - index) : 0

            section.style.transform = `translateY(${translateY}vh)`
            section.style.transition = shouldAnimate
                ? 'transform 700ms cubic-bezier(0.25, 0.46, 0.45, 0.94)'
                : 'none'
            section.style.pointerEvents = 'auto'
            section.style.opacity = '1'
            section.style.visibility = 'visible'
        })

        if (shouldAnimate) {
            const bounceTimeout = window.setTimeout(() => {
                sectionRefs.current.forEach((section, index) => {
                    if (!section || index >= activeSectionIndex) {
                        return
                    }

                    const bounceY = (-100 * (activeSectionIndex - index)) + 0.8
                    section.style.transition = 'transform 120ms cubic-bezier(0.34, 1.56, 0.64, 1)'
                    section.style.transform = `translateY(${bounceY}vh)`

                    const settleTimeout = window.setTimeout(() => {
                        section.style.transition = 'transform 80ms cubic-bezier(0.25, 0.46, 0.45, 0.94)'
                        section.style.transform = `translateY(${-100 * (activeSectionIndex - index)}vh)`
                    }, 120)
                    sectionAnimationTimeoutsRef.current.push(settleTimeout)
                })
            }, 550)

            sectionAnimationTimeoutsRef.current.push(bounceTimeout)
        }

        didMountSectionsRef.current = true

        return () => {
            clearSectionAnimationTimeouts()
        }
    }, [activeSectionIndex])

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
        return () => {
            clearSequentialNavTimeouts()
        }
    }, [])

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
        setCurrentPhotoIndex((value) => Math.min(value, Math.max(0, photos.length - mediaItemsPerView)))
        setCurrentVideoIndex((value) => Math.min(value, Math.max(0, videos.length - mediaItemsPerView)))
    }, [mediaItemsPerView, photos.length, videos.length])

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
        if (activeSectionIndex !== 1 || aboutSteps.length === 0) {
            clearAboutAnimation()
            setTypedAboutTitle('')
            setAboutSubtitleVisible(false)
            setAboutDescriptionVisible(false)
            return
        }

        const startTypingTitle = (stepIndex) => {
            const step = aboutSteps[stepIndex]
            setAboutTextIndex(stepIndex)
            setTypedAboutTitle('')
            setAboutSubtitleVisible(false)
            setAboutDescriptionVisible(false)

            if (!step) {
                return
            }

            let charIndex = 0
            const tick = () => {
                charIndex += 1
                setTypedAboutTitle(step.title.slice(0, charIndex))

                if (charIndex < step.title.length) {
                    const timeoutId = window.setTimeout(tick, 50)
                    aboutTimeoutsRef.current.push(timeoutId)
                    return
                }

                const subtitleTimeout = window.setTimeout(() => {
                    setAboutSubtitleVisible(true)
                }, 200)
                const descriptionTimeout = window.setTimeout(() => {
                    setAboutDescriptionVisible(true)
                }, 400)
                aboutTimeoutsRef.current.push(subtitleTimeout)
                aboutTimeoutsRef.current.push(descriptionTimeout)
            }

            if (!step.title) {
                setAboutSubtitleVisible(true)
                setAboutDescriptionVisible(true)
                return
            }

            const timeoutId = window.setTimeout(tick, 50)
            aboutTimeoutsRef.current.push(timeoutId)
        }

        const nextTextOrder = shuffleArray(aboutSteps.map((_, index) => index))
        const nextBgOrder = shuffleArray(aboutImages.map((_, index) => index))

        aboutTextOrderRef.current = nextTextOrder
        aboutBgOrderRef.current = nextBgOrder
        aboutTextPointerRef.current = 0
        aboutBgPointerRef.current = 0

        setAboutBgIndex(nextBgOrder[0] ?? 0)
        startTypingTitle(nextTextOrder[0] ?? 0)

        aboutTextIntervalRef.current = window.setInterval(() => {
            let nextPointer = (aboutTextPointerRef.current + 1) % Math.max(1, aboutTextOrderRef.current.length)
            if (nextPointer === 0) {
                aboutTextOrderRef.current = shuffleArray(aboutSteps.map((_, index) => index))
            }
            aboutTextPointerRef.current = nextPointer
            const stepIndex = aboutTextOrderRef.current[nextPointer] ?? 0
            startTypingTitle(stepIndex)
        }, 6000)

        aboutBgIntervalRef.current = window.setInterval(() => {
            let nextPointer = (aboutBgPointerRef.current + 1) % Math.max(1, aboutBgOrderRef.current.length)
            if (nextPointer === 0) {
                aboutBgOrderRef.current = shuffleArray(aboutImages.map((_, index) => index))
            }
            aboutBgPointerRef.current = nextPointer
            setAboutBgIndex(aboutBgOrderRef.current[nextPointer] ?? 0)
        }, 4000)

        return () => {
            clearAboutAnimation()
        }
    }, [activeSectionIndex, aboutImages, aboutSteps])

    useEffect(() => {
        if (activeSectionIndex === 3 && calendarScrollRef.current) {
            calendarScrollRef.current.scrollTop = 0
        }
    }, [activeSectionIndex])

    useEffect(() => {
        setPlayerDuration(0)
        setPlayerPosition(0)
        setWidgetReady(false)
    }, [activeTrack?.id])

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
            const currentIndex = activeSectionIndexRef.current

            if (index < 0 || index >= sectionIds.length || isScrollingRef.current || index === currentIndex) {
                return
            }
            if (animate) {
                isScrollingRef.current = true
                setIsScrolling(true)
                window.setTimeout(() => {
                    isScrollingRef.current = false
                    setIsScrolling(false)
                }, 700)
            }
            setActiveSectionIndex(index)
            activeSectionIndexRef.current = index
            window.history.replaceState(null, '', `#${sectionIds[index]}`)
        }

        const moveDown = () => goToSection(activeSectionIndexRef.current + 1)
        const moveUp = () => goToSection(activeSectionIndexRef.current - 1)

        const onWheel = (e) => {
            e.preventDefault()
            if (isScrollingRef.current) {
                return
            }

            if (scrollCurrentSectionInternally(e.deltaY > 0 ? 1 : -1, 120)) {
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
                if (scrollCurrentSectionInternally(delta > 0 ? 1 : -1, 180)) {
                    return
                }
                if (delta > 0) {
                    moveDown()
                } else {
                    moveUp()
                }
            }
        }

        const onKeyDown = (e) => {
            if (isScrollingRef.current) {
                return
            }
            if (['ArrowDown', 'PageDown', ' '].includes(e.key)) {
                e.preventDefault()
                if (scrollCurrentSectionInternally(1, 180)) {
                    return
                }
                moveDown()
            }
            if (['ArrowUp', 'PageUp'].includes(e.key)) {
                e.preventDefault()
                if (scrollCurrentSectionInternally(-1, 180)) {
                    return
                }
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
            const hash = window.location.hash.replace('#', '').split('/')[0]
            const idx = sectionIds.indexOf(hash)
            if (idx >= 0) {
                setActiveSectionIndex(idx)
                activeSectionIndexRef.current = idx
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
        if (idx < 0) {
            return
        }

        const currentIndex = activeSectionIndexRef.current
        const distance = Math.abs(idx - currentIndex)

        const goDirect = (nextIndex) => {
            setActiveSectionIndex(nextIndex)
            activeSectionIndexRef.current = nextIndex
            window.history.replaceState(null, '', `#${sectionIds[nextIndex]}`)
            if (sectionIds[nextIndex] === 'calendar' && calendarScrollRef.current) {
                calendarScrollRef.current.scrollTop = 0
            }
        }

        if (distance <= 1) {
            goDirect(idx)
            return
        }

        clearSequentialNavTimeouts()
        const direction = idx > currentIndex ? 1 : -1

        let step = currentIndex
        const moveNext = () => {
            if (step === idx) {
                return
            }

            step += direction
            goDirect(step)

            if (step !== idx) {
                const timeoutId = window.setTimeout(moveNext, 800)
                sequentialNavTimeoutsRef.current.push(timeoutId)
            }
        }

        moveNext()
    }
    const handleScrollIndicator = () => {
        if (scrollCurrentSectionInternally(1, Math.max(180, Math.round(window.innerHeight * 0.85)))) {
            return
        }
        goTo(sectionIds[Math.min(sectionIds.length - 1, activeSectionIndex + 1)])
    }

    const maxPhotoIndex = Math.max(0, photos.length - mediaItemsPerView)
    const maxVideoIndex = Math.max(0, videos.length - mediaItemsPerView)
    const maxMusicOffset = Math.max(0, tracks.length - musicItemsPerView)
    const musicOffset = Math.min(maxMusicOffset, Math.max(0, currentTrack - Math.floor(musicItemsPerView / 2)))
    const scrollCurrentSectionInternally = (direction, amount = 120) => {
        const container = activeSectionIndex === 3 ? calendarScrollRef.current : null

        if (!container) {
            return false
        }

        const maxScrollTop = Math.max(0, container.scrollHeight - container.clientHeight)

        if (direction > 0 && container.scrollTop < maxScrollTop - 4) {
            container.scrollTop = Math.min(maxScrollTop, container.scrollTop + amount)
            return true
        }

        if (direction < 0 && container.scrollTop > 4) {
            container.scrollTop = Math.max(0, container.scrollTop - amount)
            return true
        }

        return false
    }
    const getSectionStyle = (index) => {
        return {
            transform: index < activeSectionIndex ? `translateY(-${(activeSectionIndex - index) * 100}vh)` : 'translateY(0vh)',
            pointerEvents: 'auto',
            opacity: 1,
            visibility: 'visible',
        }
    }

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
                locales={locales}
                currentPath={currentPath}
                intro={legacy.intro}
                footer={legacy.footer}
                socialLinks={footerSocialLinks}
                onTogglePlay={() => setIsPlaying((v) => !v)}
                onToggleMute={() => setIsMuted((v) => !v)}
                isMuted={isMuted}
                isPlaying={isPlaying}
            />

            <LegacyHeader
                locale={locale}
                locales={locales}
                currentPath={currentPath}
                menu={legacy.header?.menu}
                socialLinks={footerSocialLinks}
                legalLabels={policyContent}
                isPlaying={isPlaying}
                isMuted={isMuted}
                onTogglePlay={() => setIsPlaying((v) => !v)}
                onToggleMute={() => setIsMuted((v) => !v)}
                onGoTo={goTo}
                onOpenLegalModal={openLegalModal}
            />

            <div className="fullpage-container">
                <div className="fullpage-wrapper">
                    <section id="home" ref={(element) => { sectionRefs.current[0] = element }} className={`legacy-section ${activeSectionIndex === 0 ? 'is-active' : ''}`} style={getSectionStyle(0)}>
                        <div className="legacy-animated-bg" />
                        <div className="legacy-starshine-layer">
                            <LegacyStarshine />
                        </div>
                        {slides.map((slide, idx) => (
                            <div key={`${slide.title}-${idx}`} className={`legacy-home-slide ${idx === currentSlide ? 'active' : ''}`}>
                                <div className="legacy-home-content">
                                    <div className="legacy-home-content-inner">
                                        {slide.logo && slide.logoPosition === 'left' ? (
                                            <div className="legacy-home-heading">
                                                <div className="legacy-home-logo-box">
                                                    <img src={slide.logo} alt={`${slide.title} Logo`} className="legacy-home-logo left" />
                                                </div>
                                                <div className="legacy-home-title-box">
                                                    <h1 className="legacy-home-title left">{slide.title}</h1>
                                                </div>
                                            </div>
                                        ) : (
                                            <>
                                                {slide.logo && (
                                                    <img src={slide.logo} alt={`${slide.title} Logo`} className="legacy-home-logo top" />
                                                )}
                                                <h1 className="legacy-home-title">{slide.title}</h1>
                                            </>
                                        )}
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

                    <section id="about" ref={(element) => { sectionRefs.current[1] = element }} className={`legacy-section ${activeSectionIndex === 1 ? 'is-active' : ''}`} style={getSectionStyle(1)}>
                        <div className="legacy-animated-bg" />
                        {aboutImages.map((image, idx) => (
                            <div
                                key={`about-bg-${idx}`}
                                className={`legacy-about-bg ${idx === aboutBgIndex ? 'active' : ''}`}
                                style={{ backgroundImage: `url(${image})` }}
                            />
                        ))}
                        {activeAboutStep && (
                            <div className="legacy-about-copy-layer">
                                <div className="legacy-about-content">
                                    <h1 className="legacy-about-title">{typedAboutTitle}</h1>
                                    <h2 className={`legacy-about-subtitle ${aboutSubtitleVisible ? 'is-visible' : ''}`}>{activeAboutStep.subtitle}</h2>
                                    <p className={`legacy-about-description ${aboutDescriptionVisible ? 'is-visible' : ''}`}>{activeAboutStep.content}</p>
                                </div>
                            </div>
                        )}
                    </section>

                    <section id="music" ref={(element) => { sectionRefs.current[2] = element }} className={`legacy-section ${activeSectionIndex === 2 ? 'is-active' : ''}`} style={getSectionStyle(2)}>
                        <div className="legacy-animated-bg" />
                        {activeTrack && (
                            <>
                                <div className="legacy-music-main-image-wrap">
                                    <div className="legacy-music-main-image" style={{ backgroundImage: `url(${activeTrack.image})` }} />
                                    <div className="legacy-music-main-image-overlay" />
                                </div>
                                <div className="legacy-music-content">
                                    <div className="legacy-music-stage">
                                        <div className="legacy-music-copy">
                                            <h1 className="legacy-music-title">{activeTrack.heroTitle}</h1>
                                            <p className="legacy-music-description">{activeTrack.description}</p>
                                        </div>
                                        <div className="legacy-music-player-card">
                                            <div className="legacy-player-row desktop">
                                                <div className="legacy-player-meta">
                                                    <h3>{activeTrack.title}</h3>
                                                    <p>{activeTrack.subtitle}</p>
                                                </div>
                                                <div className="legacy-player-controls">
                                                    <button onClick={goPrevTrack} aria-label="Pista anterior">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                            <path d="M6 6h2v12H6zm3.5 6 8.5 6V6z" />
                                                        </svg>
                                                    </button>
                                                    <button onClick={() => setIsPlaying((v) => !v)} aria-label={isPlaying ? 'Pausar' : 'Reproducir'}>
                                                        {isPlaying ? (
                                                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" />
                                                            </svg>
                                                        ) : (
                                                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                                <path d="M8 5v14l11-7z" />
                                                            </svg>
                                                        )}
                                                    </button>
                                                    <button onClick={goNextTrack} aria-label="Pista siguiente">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                            <path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div className="legacy-player-side-actions">
                                                    <span>{formatPlayerTime(playerPosition)}</span>
                                                    <button onClick={() => setIsMuted((v) => !v)} aria-label={isMuted ? 'Activar sonido' : 'Silenciar'}>
                                                        {isMuted ? (
                                                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                                <path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .88-.15 1.72-.43 2.5l1.51 1.51A8.92 8.92 0 0 0 21 12a8.94 8.94 0 0 0-3-6.71l-1.42 1.42A6.96 6.96 0 0 1 19 12zM4.27 3 3 4.27l4.73 4.73H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.45.94-2.31 1.21v2.06a8.94 8.94 0 0 0 3.76-1.76L19.73 21 21 19.73 12 10.73 4.27 3zM12 4 9.91 6.09 12 8.18V4z" />
                                                            </svg>
                                                        ) : (
                                                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                                <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05A4.98 4.98 0 0 0 16.5 12zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z" />
                                                            </svg>
                                                        )}
                                                    </button>
                                                    <button onClick={() => openTrackLink(activeTrack.soundcloudUrl)} aria-label="Abrir track en SoundCloud">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                            <path d="M12 21.35 10.55 20C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54Z" />
                                                        </svg>
                                                    </button>
                                                    <button onClick={() => openTrackLink(findSocialLink(contactSocialLinks, 'soundcloud').url)} aria-label="Seguir en SoundCloud">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4Zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z" />
                                                        </svg>
                                                    </button>
                                                    <button onClick={handleShareTrack} aria-label="Compartir track">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                            <path d="M18 16.08a3 3 0 1 0-2.83-4l-6.02-3.01a3 3 0 0 0 0-1.16l6.02-3.01a3 3 0 1 0-.89-1.79 3.1 3.1 0 0 0 .05.54L8.31 6.66a3 3 0 1 0 0 4.68l6.02 3.01a3.1 3.1 0 0 0-.05.54A3 3 0 1 0 18 16.08Z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div className="legacy-player-row mobile">
                                                <div className="legacy-player-mobile-meta">
                                                    <h3>{activeTrack.title}</h3>
                                                    <p>{activeTrack.subtitle}</p>
                                                    <span>{formatPlayerTime(playerPosition)}</span>
                                                </div>
                                                <div className="legacy-player-controls">
                                                    <button onClick={goPrevTrack} aria-label="Pista anterior">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                            <path d="M6 6h2v12H6zm3.5 6 8.5 6V6z" />
                                                        </svg>
                                                    </button>
                                                    <button onClick={() => setIsPlaying((v) => !v)} aria-label={isPlaying ? 'Pausar' : 'Reproducir'}>
                                                        {isPlaying ? (
                                                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" />
                                                            </svg>
                                                        ) : (
                                                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                                <path d="M8 5v14l11-7z" />
                                                            </svg>
                                                        )}
                                                    </button>
                                                    <button onClick={goNextTrack} aria-label="Pista siguiente">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                            <path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z" />
                                                        </svg>
                                                    </button>
                                                    <button onClick={() => setIsMuted((v) => !v)} aria-label={isMuted ? 'Activar sonido' : 'Silenciar'}>
                                                        {isMuted ? (
                                                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                                <path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .88-.15 1.72-.43 2.5l1.51 1.51A8.92 8.92 0 0 0 21 12a8.94 8.94 0 0 0-3-6.71l-1.42 1.42A6.96 6.96 0 0 1 19 12zM4.27 3 3 4.27l4.73 4.73H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.45.94-2.31 1.21v2.06a8.94 8.94 0 0 0 3.76-1.76L19.73 21 21 19.73 12 10.73 4.27 3zM12 4 9.91 6.09 12 8.18V4z" />
                                                            </svg>
                                                        ) : (
                                                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                                <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05A4.98 4.98 0 0 0 16.5 12zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z" />
                                                            </svg>
                                                        )}
                                                    </button>
                                                </div>
                                                <div className="legacy-player-mobile-actions">
                                                    <button onClick={() => openTrackLink(activeTrack.soundcloudUrl)} aria-label="Abrir track en SoundCloud">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                            <path d="M12 21.35 10.55 20C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54Z" />
                                                        </svg>
                                                    </button>
                                                    <button onClick={() => openTrackLink(findSocialLink(contactSocialLinks, 'soundcloud').url)} aria-label="Seguir en SoundCloud">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4Zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z" />
                                                        </svg>
                                                    </button>
                                                    <button onClick={handleShareTrack} aria-label="Compartir track">
                                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                            <path d="M18 16.08a3 3 0 1 0-2.83-4l-6.02-3.01a3 3 0 0 0 0-1.16l6.02-3.01a3 3 0 1 0-.89-1.79 3.1 3.1 0 0 0 .05.54L8.31 6.66a3 3 0 1 0 0 4.68l6.02 3.01a3.1 3.1 0 0 0-.05.54A3 3 0 1 0 18 16.08Z" />
                                                        </svg>
                                                    </button>
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
                                        </div>
                                        <div className="legacy-track-carousel-wrap">
                                            <button className="legacy-carousel-btn left" onClick={goPrevTrack} aria-label="Mover carrusel a la izquierda">‹</button>
                                            <div
                                                className="legacy-track-carousel"
                                                onTouchStart={(e) => setMusicTouchStartX(e.touches[0].clientX)}
                                                onTouchEnd={(e) => {
                                                    const delta = musicTouchStartX - e.changedTouches[0].clientX
                                                    if (Math.abs(delta) < 50) return
                                                    if (delta > 0) {
                                                        goNextTrack()
                                                    } else {
                                                        goPrevTrack()
                                                    }
                                                }}
                                            >
                                                <div className="legacy-track-cards" style={{ transform: `translateX(-${musicOffset * (100 / musicItemsPerView)}%)` }}>
                                                    {tracks.map((track, idx) => (
                                                        <button key={track.id} className={`legacy-track-card ${idx === currentTrack ? 'active' : ''}`} style={{ flex: `0 0 calc(${100 / musicItemsPerView}% - 10px)` }} onClick={() => setCurrentTrack(idx)}>
                                                            <div className="legacy-track-card-shell">
                                                                <img src={track['label-img']} alt={track.title} />
                                                                <div>
                                                                    <h4>{track.title}</h4>
                                                                    <p>{track.subtitle}</p>
                                                                </div>
                                                            </div>
                                                            <span className="legacy-track-indicator" />
                                                        </button>
                                                    ))}
                                                </div>
                                            </div>
                                            <button className="legacy-carousel-btn right" onClick={goNextTrack} aria-label="Mover carrusel a la derecha">›</button>
                                        </div>
                                        <div className="legacy-soundcloud-hidden">
                                            <iframe
                                                ref={soundcloudRef}
                                                key={activeTrack.id}
                                                title={activeTrack.title}
                                                src={activeTrack.soundcloudUrl}
                                                className="legacy-soundcloud"
                                                allow="autoplay"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </>
                        )}
                    </section>

                    <section id="calendar" ref={(element) => { sectionRefs.current[3] = element }} className={`legacy-section ${activeSectionIndex === 3 ? 'is-active' : ''}`} style={getSectionStyle(3)}>
                        <div className="legacy-animated-bg" />
                        <div className="legacy-events-bg" />
                        <div className="legacy-events-content">
                            <h2 className="legacy-events-title">{calendarData.translations?.title ?? legacy.header?.menu?.calendarEvents}</h2>
                            <div ref={calendarScrollRef} className="legacy-events-list">
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

                    <section id="media" ref={(element) => { sectionRefs.current[4] = element }} className={`legacy-section ${activeSectionIndex === 4 ? 'is-active' : ''}`} style={getSectionStyle(4)}>
                        <div className="legacy-animated-bg" />
                        <div className="legacy-media-content">
                            <h2 className="legacy-media-title">{legacy.media?.title ?? 'MEDIA'}</h2>
                            <div className="legacy-media-stack">
                                <MediaCarouselRow
                                    items={photos}
                                    itemType="photo"
                                    currentIndex={currentPhotoIndex}
                                    setCurrentIndex={setCurrentPhotoIndex}
                                    maxIndex={maxPhotoIndex}
                                    perView={mediaItemsPerView}
                                    label={legacy.media?.photoLabel ?? 'Foto'}
                                    onOpen={openPhotoLightbox}
                                />
                                <MediaCarouselRow
                                    items={videos}
                                    itemType="video"
                                    currentIndex={currentVideoIndex}
                                    setCurrentIndex={setCurrentVideoIndex}
                                    maxIndex={maxVideoIndex}
                                    perView={mediaItemsPerView}
                                    label={legacy.media?.videoLabel ?? 'Video'}
                                    onOpen={openVideoLightbox}
                                />
                                <div className="legacy-media-cta-wrap">
                                    <a
                                        href={legacy.media?.youtubeChannelUrl ?? 'https://www.youtube.com/channel/TUCANALAQUI'}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="legacy-media-cta"
                                    >
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z" />
                                        </svg>
                                        <span>{legacy.media?.viewMoreOnYoutube ?? 'Ver mas en YouTube'}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="contact" ref={(element) => { sectionRefs.current[5] = element }} className={`legacy-section ${activeSectionIndex === 5 ? 'is-active' : ''}`} style={getSectionStyle(5)}>
                        <div className="legacy-animated-bg" />
                        <div className="legacy-contact-bg" />
                        <div className="legacy-contact-content">
                            <div className="legacy-social">
                                <a href={findSocialLink(contactSocialLinks, 'facebook').url} target="_blank" rel="noreferrer" aria-label={findSocialLink(contactSocialLinks, 'facebook').label ?? 'Facebook'}>
                                    <FaFacebookSquare />
                                </a>
                                <a href={findSocialLink(contactSocialLinks, 'instagram').url} target="_blank" rel="noreferrer" aria-label={findSocialLink(contactSocialLinks, 'instagram').label ?? 'Instagram'}>
                                    <AiFillInstagram />
                                </a>
                                <a href={findSocialLink(contactSocialLinks, 'soundcloud').url} target="_blank" rel="noreferrer" aria-label={findSocialLink(contactSocialLinks, 'soundcloud').label ?? 'SoundCloud'}>
                                    <ImSoundcloud2 />
                                </a>
                                <a href={findSocialLink(contactSocialLinks, 'spotify').url} target="_blank" rel="noreferrer" aria-label={findSocialLink(contactSocialLinks, 'spotify').label ?? 'Spotify'}>
                                    <FaSpotify />
                                </a>
                            </div>
                            <div className="legacy-sponsors">
                                <div className="legacy-sponsors-box">
                                    <div className="legacy-sponsors-track">
                                        {[...sponsorLogos, ...sponsorLogos].map((logo, idx) => (
                                            <a key={`${logo.name}-${idx}`} href={logo.url} target="_blank" rel="noreferrer">
                                                <img src={logo.imgSrc} alt={logo.name} />
                                            </a>
                                        ))}
                                    </div>
                                </div>
                            </div>
                            <div className="legacy-contact-marquee">
                                <ContactMarqueeRow rowClass="a" words={marqueeRows[0] ?? []} />
                                <ContactMarqueeRow rowClass="b" words={marqueeRows[1] ?? []} />
                                <ContactMarqueeRow rowClass="c" words={marqueeRows[2] ?? []} />
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <footer className="legacy-fixed-footer pointer-events-auto hidden lg:grid">
                <div className="legacy-fixed-footer-left">
                    <span>{legacy.footer?.copyright ?? '© 2025 Copyright.'}</span>
                    <span>{legacy.footer?.rights ?? 'Domo Digital Studio'}</span>
                </div>
                <div className="legacy-fixed-footer-center">
                    <button onClick={() => setPolicyModal({ title: policyContent.terms?.title, content: policyContent.terms?.content })}>
                        {policyContent.terms_button ?? 'Términos y Condiciones'}
                    </button>
                    <button onClick={() => setPolicyModal({ title: policyContent.privacy?.title, content: policyContent.privacy?.content })}>
                        {policyContent.privacy_button ?? 'Política de Privacidad'}
                    </button>
                    <button onClick={() => setPolicyModal({ title: policyContent.cookies?.title, content: policyContent.cookies?.content })}>
                        {policyContent.cookies_button ?? 'Política de Cookies'}
                    </button>
                </div>
                <div className="legacy-fixed-footer-right">
                    <a href={findSocialLink(footerSocialLinks, 'facebook').url} target="_blank" rel="noreferrer" aria-label={findSocialLink(footerSocialLinks, 'facebook').label ?? 'Facebook'}><FaFacebookSquare /></a>
                    <a href={findSocialLink(footerSocialLinks, 'instagram').url} target="_blank" rel="noreferrer" aria-label={findSocialLink(footerSocialLinks, 'instagram').label ?? 'Instagram'}><AiFillInstagram /></a>
                    <a href={findSocialLink(footerSocialLinks, 'soundcloud').url} target="_blank" rel="noreferrer" aria-label={findSocialLink(footerSocialLinks, 'soundcloud').label ?? 'SoundCloud'}><ImSoundcloud2 /></a>
                    <a href={findSocialLink(footerSocialLinks, 'spotify').url} target="_blank" rel="noreferrer" aria-label={findSocialLink(footerSocialLinks, 'spotify').label ?? 'Spotify'}><FaSpotify /></a>
                </div>
            </footer>

            {activeSectionIndex < sectionIds.length - 1 && (
                <button className="legacy-scroll-indicator" onClick={handleScrollIndicator}>
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
