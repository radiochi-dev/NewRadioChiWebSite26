import { router } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import LanguageSwitcher from '../LanguageSwitcher'
import { FaFacebookF, FaInstagram, FaSoundcloud } from 'react-icons/fa'

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
    const [authOpen, setAuthOpen] = useState(false)
    const [authAccepted, setAuthAccepted] = useState(false)
    const [authEmail, setAuthEmail] = useState('')
    const [authPassword, setAuthPassword] = useState('')
    const [authLoading, setAuthLoading] = useState(false)
    const [authError, setAuthError] = useState('')
    const [isRegisterForm, setIsRegisterForm] = useState(false)

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
                setAuthOpen(false)
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
                        <button onClick={onTogglePlay} className={`legacy-header-icon ${isPlaying ? '' : 'active'}`}>
                            {isPlaying ? '❚❚' : '▶'}
                        </button>
                        <button onClick={onToggleMute} className={`legacy-header-icon ${isMuted ? 'active' : ''}`}>
                            {isMuted ? '🔇' : '🔊'}
                        </button>
                        <div className={`legacy-lang-wrap ${menuOpen ? 'hidden' : ''}`}>
                            <LanguageSwitcher currentLocale={locale} locales={locales} currentPath={currentPath} />
                        </div>
                        <button className="legacy-auth-btn" onClick={() => setAuthOpen(true)}>
                            Login/Register
                        </button>
                        <button className={`legacy-menu-toggle ${menuOpen ? 'active' : ''}`} onClick={() => setMenuOpen((v) => !v)} aria-label="Open menu">
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
                    <a href="#media" onClick={(e) => (e.preventDefault(), go('media'))}>{menu?.media}</a>
                    <a href="#contact" onClick={(e) => (e.preventDefault(), go('contact'))}>{menu?.contact}</a>
                    <button className="legacy-side-auth-btn" onClick={() => { setMenuOpen(false); setAuthOpen(true) }}>Login/Register</button>
                    <div className="legacy-side-lang">
                        <LanguageSwitcher currentLocale={locale} locales={locales} currentPath={currentPath} mode="list" />
                    </div>
                    <div className="legacy-side-social">
                        <a href="https://www.facebook.com/fernandocardonatoro" target="_blank" rel="noreferrer" aria-label="Facebook">
                            <FaFacebookF />
                        </a>
                        <a href="https://www.instagram.com/mrchiloveyou/" target="_blank" rel="noreferrer" aria-label="Instagram">
                            <FaInstagram />
                        </a>
                        <a href="https://soundcloud.com/mrchi1" target="_blank" rel="noreferrer" aria-label="SoundCloud">
                            <FaSoundcloud />
                        </a>
                    </div>
                </nav>
            </aside>

            {authOpen && (
                <div className="legacy-auth-modal" onClick={() => setAuthOpen(false)}>
                    <div className="legacy-auth-card" onClick={(e) => e.stopPropagation()}>
                        <button className="legacy-auth-close" onClick={() => setAuthOpen(false)}>×</button>
                        <h3>{isRegisterForm ? 'Crear cuenta' : 'Acceso CMS'}</h3>
                        <p>{isRegisterForm ? 'Registro de acceso (deshabilitado temporalmente)' : 'Accede al panel de administración para gestionar contenidos.'}</p>
                        <form
                            className="legacy-auth-form"
                            onSubmit={(e) => {
                                e.preventDefault()
                                if (!authAccepted) {
                                    setAuthError('Debes aceptar los términos legales para continuar.')
                                    return
                                }
                                if (isRegisterForm) {
                                    setAuthError('Registro deshabilitado temporalmente.')
                                    return
                                }
                                setAuthLoading(true)
                                setAuthError('')
                                router.post('/login', {
                                    email: authEmail,
                                    password: authPassword,
                                    accepted_legal: authAccepted ? 1 : 0,
                                }, {
                                    preserveScroll: true,
                                    onSuccess: () => {
                                        setAuthOpen(false)
                                        setAuthPassword('')
                                    },
                                    onError: (errors) => {
                                        setAuthError(errors.email || errors.password || errors.accepted_legal || 'No se pudo iniciar sesión.')
                                    },
                                    onFinish: () => {
                                        setAuthLoading(false)
                                    },
                                })
                            }}
                        >
                            <input type="email" placeholder="Email" value={authEmail} onChange={(e) => setAuthEmail(e.target.value)} required />
                            <input type="password" placeholder="Password" value={authPassword} onChange={(e) => setAuthPassword(e.target.value)} required />
                            <label className="legacy-auth-legal">
                                <input type="checkbox" checked={authAccepted} onChange={(e) => setAuthAccepted(e.target.checked)} />
                                <span>Acepto los términos legales</span>
                            </label>
                            <div className="legacy-auth-legal-links">
                                <button type="button" onClick={() => onOpenLegalModal?.('terms')}>Términos y Condiciones</button>
                                <button type="button" onClick={() => onOpenLegalModal?.('privacy')}>Política de Privacidad</button>
                                <button type="button" onClick={() => onOpenLegalModal?.('cookies')}>Política de Cookies</button>
                            </div>
                            {authError && <div className="legacy-auth-error">{authError}</div>}
                            <button type="submit" className="legacy-auth-submit" disabled={authLoading}>
                                {authLoading ? 'Validando...' : isRegisterForm ? 'Crear cuenta' : 'Iniciar sesión'}
                            </button>
                        </form>
                    </div>
                </div>
            )}
        </>
    )
}
