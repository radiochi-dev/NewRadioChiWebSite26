import { useEffect, useState } from 'react'
import { AiFillInstagram } from 'react-icons/ai'
import { FaFacebookSquare, FaSpotify } from 'react-icons/fa'
import { ImSoundcloud2 } from 'react-icons/im'
import LanguageSwitcher from '../LanguageSwitcher'

export default function LegacyIntro({ locale, locales, currentPath, intro, footer, onTogglePlay, onToggleMute, isMuted, isPlaying }) {
    const [visible, setVisible] = useState(false)
    const [zoomOut, setZoomOut] = useState(false)
    const seenKey = 'radiochiIntroSeen'

    useEffect(() => {
        const seen = localStorage.getItem(seenKey) === 'true' || document.cookie.includes(`${seenKey}=true`)
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
                document.cookie = `${seenKey}=true; max-age=31536000; path=/; SameSite=Lax`
                window.dispatchEvent(new Event('introFinished'))
            }, 320)
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
            <div className="legacy-intro-toolbar">
                <button onClick={(e) => (e.stopPropagation(), onTogglePlay())} className="legacy-header-icon" aria-label="Reproducir/Pausar música">
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
                <button onClick={(e) => (e.stopPropagation(), onToggleMute())} className={`legacy-header-icon ${isMuted ? 'active' : ''}`} aria-label="Control de volumen">
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
                <LanguageSwitcher currentLocale={locale} locales={locales} currentPath={currentPath} />
            </div>
            <div className="legacy-intro-welcome">{intro?.welcome ?? 'Bienvenido a'}</div>
            <div className="legacy-intro-logo-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 528.38 494.34" className="legacy-intro-logo-core" aria-hidden="true">
                    <defs><style>{'.cls-1{fill:#fff;}'}</style></defs>
                    <g id="Capa_2" data-name="Capa 2">
                        <g id="Capa_1-2" data-name="Capa 1">
                            <path className="cls-1" d="M283,164.77l-10.92,1.53v61.3l10.2-1.44c8.1-1.14,14.19-4.72,18.62-10.95h0c4.48-6.29,6.75-14.24,6.75-23.62s-2.21-16.47-6.58-21.38S290.91,163.66,283,164.77Z" />
                            <path className="cls-1" d="M104,192.87c-2.33-1.86-5.54-2.48-9.83-1.87l-44.7,7v25.39l44-6.89c4.42-.62,7.78-2.23,10.27-4.93h0a14.13,14.13,0,0,0,3.64-10.08C107.38,197.5,106.28,194.68,104,192.87Z" />
                            <path className="cls-1" d="M518.69,125.66l-.88-1.48-.07-.07a60.34,60.34,0,0,0-27.32-23.34A68.2,68.2,0,0,0,463,95.28a87.45,87.45,0,0,0-12,.86,84.44,84.44,0,0,0-37.92,15c-1.82,1.31-3.59,2.69-5.29,4.13V0L350.62,154.45c-.8-1.6-1.65-3.17-2.58-4.7a59.4,59.4,0,0,0-28.43-24.36,69.28,69.28,0,0,0-26.92-5.16,90.21,90.21,0,0,0-12.32.88l-46,6.46-11.83,1.66v7.07l-19.17-55L189.72,42l-12.4,39.71-20,64.15-7.65,22.5c-.19-.31-.39-.61-.6-.91a47.07,47.07,0,0,0-23.86-18,60.54,60.54,0,0,0-20.11-3.25,80.83,80.83,0,0,0-11,.79h-.11L11.72,159.27,0,161V310.25l15.66-2.2,35.74-5,11.83-1.66v-36.1l21.93-3.44,12.59,24.64,4.46,8.72,9.69-1.37,39-5.48,19.23-2.7h0l2.28-.32a82.68,82.68,0,0,0-10.74,16.58c-6.16,12.62-9.29,27-9.29,42.81s3.16,29.47,9.39,40.46a55.25,55.25,0,0,0,27.29,24.25,69.82,69.82,0,0,0,27.21,5.08,100.7,100.7,0,0,0,14.09-1,82.73,82.73,0,0,0,33.75-12.3,79.44,79.44,0,0,0,9-6.82v11.51l15.65-2.2,35.75-5L336.36,397V361.34l6.09-3.52V396.1l15.66-2.2,32.34-4.54v105l69-248,4.73-1.14a83.4,83.4,0,0,0,55-43.24,84.33,84.33,0,0,0,9.18-38.72C528.38,149.1,525.12,136.47,518.69,125.66ZM282.21,345.25a56.56,56.56,0,0,1-8,25.24,58.89,58.89,0,0,1-18.6,19,64.47,64.47,0,0,1-26.31,9.6,79.69,79.69,0,0,1-11,.8,54.46,54.46,0,0,1-21.2-4,43.07,43.07,0,0,1-21.28-18.91c-4.86-8.56-7.32-19.17-7.32-31.54a75.41,75.41,0,0,1,7.24-33.37,63.71,63.71,0,0,1,20.87-24.41c9-6.3,19.75-10.37,32-12.09,9.8-1.38,18.72-.62,26.52,2.23a40.1,40.1,0,0,1,19,14.12,44.72,44.72,0,0,1,8.14,23.26l.19,2.42-35.41,5-.47-2.39c-1-5-3.1-8.72-6.44-11.3s-7.36-3.42-12.45-2.7A22.4,22.4,0,0,0,211.07,317c-4.09,6.37-6.16,14.49-6.16,24.14s2,17,6,22.09c3.8,4.86,9,6.74,16,5.77a21.15,21.15,0,0,0,12.87-6.48,26.55,26.55,0,0,0,6.74-13.63l.34-1.82,35.59-5Zm-45.87-82.49v-.31l-20.58,2.89-25.89-74.65-25.42,81.86L149,274.73h0l-39,5.48-17.12-33.5-43.38,6.8v35.91l-35.75,5V172.86L96,160.6c9.24-1.29,17.53-.68,24.66,1.82a33.63,33.63,0,0,1,17.06,12.8c4,5.93,6.11,13.28,6.11,21.86a45.28,45.28,0,0,1-4.94,20.78h0a50.17,50.17,0,0,1-11.69,15l9.11,17.45,34.08-100.2,20.09-64.33,20.44,58.63,25.46,68.28V141.18l45.94-6.46c11.94-1.67,22.71-.55,32,3.35a45.91,45.91,0,0,1,22,18.82c5.15,8.49,7.77,18.69,7.77,30.3a69.32,69.32,0,0,1-7.69,32.24h0A67.86,67.86,0,0,1,314.79,244A72.4,72.4,0,0,1,283,256.2Zm155.6,109.93-35.74,5V331.44l-33.59,19.39v31.61l-35.74,5V272.08l35.74-5v42.5l33.59-19.39V262.34l35.74-5Zm13.81,23v-156l-66.86,7.93L394,79.66V194.39l65-7.55ZM507,195.69a69.61,69.61,0,0,1-46,36.13l8.8-38.48c.47-.58.94-1.17,1.38-1.8,4.71-6.61,7.09-14.66,7.09-23.93s-2.32-16.42-6.91-21.58a17.8,17.8,0,0,0-14-6.18,26.32,26.32,0,0,0-3.78.28,25.92,25.92,0,0,0-18.29,11.32c-4.71,6.6-7.09,14.65-7.09,23.92,0,.83,0,1.64.06,2.44L409.58,180V132.52a70.14,70.14,0,0,1,43.27-22.77c12-1.67,22.74-.46,32.07,3.62a46.93,46.93,0,0,1,22,19.31h0c5.15,8.66,7.76,18.93,7.76,30.54A70.75,70.75,0,0,1,507,195.69Z" />
                        </g>
                    </g>
                </svg>
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
                    <a href="https://www.facebook.com/fernandocardonatoro" target="_blank" rel="noreferrer" aria-label="Facebook"><FaFacebookSquare /></a>
                    <a href="https://www.instagram.com/mrchiloveyou/" target="_blank" rel="noreferrer" aria-label="Instagram"><AiFillInstagram /></a>
                    <a href="#" target="_blank" rel="noreferrer" aria-label="SoundCloud"><ImSoundcloud2 /></a>
                    <a href="#" target="_blank" rel="noreferrer" aria-label="Spotify"><FaSpotify /></a>
                </div>
                <p>© 2025 Radiochi. {footer?.rights ?? footer?.copyright ?? ''}</p>
            </footer>
        </div>
    )
}
