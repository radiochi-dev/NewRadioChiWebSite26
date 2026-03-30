import { useEffect, useMemo, useState } from 'react'

export default function LegacyIntro({ locale, intro, footer, onTogglePlay, onToggleMute, isMuted }) {
    const [visible, setVisible] = useState(false)
    const [zoomOut, setZoomOut] = useState(false)
    const seenKey = useMemo(() => `radiochiIntroSeen-${locale}`, [locale])

    useEffect(() => {
        const seen = localStorage.getItem(seenKey) === 'true' || document.cookie.includes('radiochiIntroSeen=true')
        if (!seen) {
            setVisible(true)
        }
    }, [seenKey])

    useEffect(() => {
        if (!visible) {
            return
        }

        let touchStartY = 0
        const finish = () => {
            setZoomOut(true)
            window.setTimeout(() => {
                setVisible(false)
                setZoomOut(false)
                localStorage.setItem(seenKey, 'true')
                document.cookie = 'radiochiIntroSeen=true; max-age=31536000; path=/; SameSite=Lax'
            }, 600)
        }

        const onWheel = (e) => {
            e.preventDefault()
            finish()
        }
        const onTouchStart = (e) => {
            touchStartY = e.touches[0].clientY
        }
        const onTouchEnd = (e) => {
            if (Math.abs(touchStartY - e.changedTouches[0].clientY) > 30) {
                finish()
            }
        }
        const onKeyDown = (e) => {
            if (['ArrowDown', 'PageDown', ' ', 'Enter'].includes(e.key)) {
                e.preventDefault()
                finish()
            }
        }
        const onClick = () => finish()

        document.addEventListener('wheel', onWheel, { passive: false, capture: true })
        document.addEventListener('touchstart', onTouchStart, { passive: true, capture: true })
        document.addEventListener('touchend', onTouchEnd, { passive: true, capture: true })
        document.addEventListener('keydown', onKeyDown, { capture: true })
        document.addEventListener('click', onClick, { capture: true })

        return () => {
            document.removeEventListener('wheel', onWheel, true)
            document.removeEventListener('touchstart', onTouchStart, true)
            document.removeEventListener('touchend', onTouchEnd, true)
            document.removeEventListener('keydown', onKeyDown, true)
            document.removeEventListener('click', onClick, true)
        }
    }, [visible, seenKey])

    if (!visible) {
        return null
    }

    return (
        <div className={`legacy-intro ${zoomOut ? 'zoom-out' : ''}`}>
            <div className="legacy-intro-bg" />
            <div className="legacy-intro-topbar">
                <button onClick={(e) => (e.stopPropagation(), onTogglePlay())} className="legacy-header-icon">▶</button>
                <button onClick={(e) => (e.stopPropagation(), onToggleMute())} className={`legacy-header-icon ${isMuted ? 'active' : ''}`}>{isMuted ? '🔇' : '🔊'}</button>
            </div>
            <div className="legacy-intro-welcome">{intro?.welcome ?? 'Bienvenido a'}</div>
            <div className="legacy-intro-logo-wrap">
                <img src="/assets/img/logos/RC_Logo_negative_rainbow.png" alt="RadioChi" className="legacy-intro-logo" />
                <span className="legacy-intro-aura aura1" />
                <span className="legacy-intro-aura aura2" />
                <span className="legacy-intro-aura aura3" />
                <span className="legacy-intro-aura aura4" />
                <span className="legacy-intro-aura aura5" />
                <span className="legacy-intro-aura aura6" />
            </div>
            <div className="legacy-intro-scroll">
                <span>Scroll down</span>
                <div className="legacy-scroll-indicator">
                    <span className="legacy-scroll-dot" />
                </div>
            </div>
            <footer className="legacy-intro-footer">
                <div className="legacy-intro-social">
                    <a href="https://www.facebook.com/fernandocardonatoro" target="_blank" rel="noreferrer">Facebook</a>
                    <a href="https://www.instagram.com/mrchiloveyou/" target="_blank" rel="noreferrer">Instagram</a>
                    <a href="https://soundcloud.com/mrchi1" target="_blank" rel="noreferrer">SoundCloud</a>
                </div>
                <p>© 2025 Radiochi. {footer?.rights ?? footer?.copyright ?? ''}</p>
            </footer>
        </div>
    )
}
