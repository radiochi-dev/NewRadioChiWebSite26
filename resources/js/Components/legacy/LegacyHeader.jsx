import { useEffect, useState } from 'react'
import LanguageSwitcher from '../LanguageSwitcher'
import { AiFillInstagram } from 'react-icons/ai'
import { FaFacebookSquare, FaSpotify } from 'react-icons/fa'
import { ImSoundcloud2 } from 'react-icons/im'

export default function LegacyHeader({
    locale,
    locales,
    currentPath,
    menu,
    isPlaying,
    isMuted,
    onTogglePlay,
    onToggleMute,
    onGoTo,
    onOpenLegalModal,
}) {
    const [menuOpen, setMenuOpen] = useState(false)
    const [blurActive, setBlurActive] = useState(false)

    useEffect(() => {
        let timeoutId = null
        const trigger = () => {
            setBlurActive(true)
            if (timeoutId) {
                clearTimeout(timeoutId)
            }
            timeoutId = window.setTimeout(() => setBlurActive(false), 250)
        }

        const onKeyDown = (e) => {
            if (['ArrowDown', 'PageDown', ' ', 'ArrowUp', 'PageUp'].includes(e.key)) {
                trigger()
            }
            if (e.key === 'Escape') {
                setMenuOpen(false)
            }
        }

        window.addEventListener('scroll', trigger, { passive: true })
        window.addEventListener('wheel', trigger, { passive: true, capture: true })
        window.addEventListener('touchend', trigger, { passive: true, capture: true })
        document.addEventListener('keydown', onKeyDown, { capture: true })

        return () => {
            if (timeoutId) {
                clearTimeout(timeoutId)
            }
            window.removeEventListener('scroll', trigger)
            window.removeEventListener('wheel', trigger, true)
            window.removeEventListener('touchend', trigger, true)
            document.removeEventListener('keydown', onKeyDown, true)
        }
    }, [])

    const go = (id) => {
        onGoTo(id)
        setMenuOpen(false)
    }

    return (
        <>
            <header className={`legacy-header ${blurActive ? 'blur-active' : ''}`}>
                <div className="legacy-header-inner">
                    <a href="#home" onClick={(e) => (e.preventDefault(), go('home'))} className="legacy-header-logo-link">
                        <img src="/assets/img/logos/RC_Logo_white.png" alt="RadioChi" className="legacy-header-logo" />
                    </a>
                    <div className="legacy-header-right">
                        <button onClick={onTogglePlay} className="legacy-header-icon" aria-label="Reproducir/Pausar música">
                            {isPlaying ? (
                                <svg className="legacy-header-svg active" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" />
                                </svg>
                            ) : (
                                <svg className="legacy-header-svg" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                            )}
                        </button>
                        <button onClick={onToggleMute} className={`legacy-header-icon ${isMuted ? 'active' : ''}`} aria-label="Control de volumen">
                            {isMuted ? (
                                <svg className="legacy-header-svg active" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M16.5 12c0-1.77-1-3.29-2.45-4.03v2.21l2.36 2.36c.05-.17.09-.35.09-.54zM19 12c0 .94-.2 1.82-.55 2.64l1.51 1.51A8.91 8.91 0 0021 12c0-3.72-2.14-6.94-5.25-8.47v2.21A6.98 6.98 0 0119 12zM4.27 3 3 4.27l4.73 4.73H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.21v2.06a8.94 8.94 0 003.73-1.78L19.73 21 21 19.73 12 10.73 4.27 3zM12 4 9.91 6.09 12 8.18V4z" />
                                </svg>
                            ) : (
                                <svg className="legacy-header-svg" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1-3.29-2.45-4.03v8.05A4.985 4.985 0 0016.5 12zM14.05 3.23v2.06A8.968 8.968 0 0119 12c0 2.93-1.4 5.53-3.55 7.17v2.06C18.83 19.65 21 16.05 21 12s-2.17-7.65-5.55-8.77z" />
                                </svg>
                            )}
                        </button>
                        <div className={`legacy-lang-wrap ${menuOpen ? 'hidden' : ''}`}>
                            <LanguageSwitcher currentLocale={locale} locales={locales} currentPath={currentPath} />
                        </div>
                        <button className={`legacy-menu-toggle ${menuOpen ? 'active' : ''}`} onClick={() => setMenuOpen((v) => !v)} aria-label="Abrir menú">
                            <span />
                            <span />
                            <span />
                        </button>
                    </div>
                </div>
            </header>

            <div className={`legacy-side-overlay ${menuOpen ? 'active' : ''}`} onClick={() => setMenuOpen(false)} />
            <aside className={`legacy-side-menu ${menuOpen ? 'active' : ''}`}>
                <nav className="legacy-side-nav">
                    <a href="#home" onClick={(e) => (e.preventDefault(), go('home'))}>{menu?.home}</a>
                    <a href="#about" onClick={(e) => (e.preventDefault(), go('about'))}>{menu?.about}</a>
                    <a href="#music" onClick={(e) => (e.preventDefault(), go('music'))}>{menu?.music}</a>
                    <a href="#calendar" onClick={(e) => (e.preventDefault(), go('calendar'))}>{menu?.calendarEvents}</a>
                    <a href="#contact" onClick={(e) => (e.preventDefault(), go('contact'))}>{menu?.contact}</a>
                    <div className="legacy-side-divider" />
                    <button className="legacy-side-policy" onClick={() => { setMenuOpen(false); onOpenLegalModal?.('terms') }}>Términos y Condiciones</button>
                    <button className="legacy-side-policy" onClick={() => { setMenuOpen(false); onOpenLegalModal?.('privacy') }}>Política de Privacidad</button>
                    <button className="legacy-side-policy" onClick={() => { setMenuOpen(false); onOpenLegalModal?.('cookies') }}>Política de Cookies</button>
                    <div className="legacy-side-lang">
                        <LanguageSwitcher currentLocale={locale} locales={locales} currentPath={currentPath} mode="list" />
                    </div>
                    <div className="legacy-side-social">
                        <a href="https://www.facebook.com/fernandocardonatoro" target="_blank" rel="noreferrer" aria-label="Facebook">
                            <FaFacebookSquare />
                        </a>
                        <a href="https://www.instagram.com/mrchiloveyou/" target="_blank" rel="noreferrer" aria-label="Instagram">
                            <AiFillInstagram />
                        </a>
                        <a href="#" target="_blank" rel="noreferrer" aria-label="SoundCloud">
                            <ImSoundcloud2 />
                        </a>
                        <a href="#" target="_blank" rel="noreferrer" aria-label="Spotify">
                            <FaSpotify />
                        </a>
                    </div>
                </nav>
            </aside>
        </>
    )
}
