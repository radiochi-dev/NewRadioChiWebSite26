import { Link } from '@inertiajs/react'
import { useEffect, useMemo, useRef, useState } from 'react'

function localePath(locale, currentPath) {
    const normalizedPath = typeof currentPath === 'string' && currentPath.trim() !== '' ? currentPath : '/'
    const cleanedPath = normalizedPath.replace(/^\/(es|en|ca|fr|it|de)(?=\/|$)/, '') || '/'

    return `/${locale}${cleanedPath === '/' ? '' : cleanedPath}`
}

export default function LanguageSwitcher({ currentLocale, locales, currentPath, mode = 'dropdown' }) {
    const [open, setOpen] = useState(false)
    const wrapperRef = useRef(null)
    const closeTimeoutRef = useRef(null)
    const others = useMemo(() => locales.filter((locale) => locale !== currentLocale), [locales, currentLocale])

    useEffect(() => {
        const onOutside = (event) => {
            if (!wrapperRef.current?.contains(event.target)) {
                setOpen(false)
            }
        }
        const onEsc = (event) => {
            if (event.key === 'Escape') {
                setOpen(false)
            }
        }
        document.addEventListener('click', onOutside)
        document.addEventListener('keydown', onEsc)
        return () => {
            document.removeEventListener('click', onOutside)
            document.removeEventListener('keydown', onEsc)
            if (closeTimeoutRef.current) {
                clearTimeout(closeTimeoutRef.current)
            }
        }
    }, [])

    const scheduleClose = () => {
        if (closeTimeoutRef.current) {
            clearTimeout(closeTimeoutRef.current)
        }
        closeTimeoutRef.current = window.setTimeout(() => setOpen(false), 1200)
    }

    const cancelClose = () => {
        if (closeTimeoutRef.current) {
            clearTimeout(closeTimeoutRef.current)
            closeTimeoutRef.current = null
        }
    }

    if (mode === 'list') {
        return (
            <div className="flex flex-wrap gap-1">
                {locales.map((locale) => (
                    <Link
                        key={locale}
                        href={localePath(locale, currentPath)}
                        className={`rounded px-0.5 py-1 text-md font-bold transition-colors duration-300 ease-in-out ${
                            locale === currentLocale ? 'text-white' : 'text-white/70 hover:text-gray-900'
                        }`}
                    >
                        {locale.toUpperCase()}
                    </Link>
                ))}
            </div>
        )
    }

    return (
        <div ref={wrapperRef} className="vlt-language-switcher relative mr-1 flex items-center justify-center text-left" onMouseEnter={cancelClose} onMouseLeave={scheduleClose}>
            <button
                type="button"
                className="language-dropdown-btn flex items-center justify-center gap-0 bg-transparent px-1 py-0.5 text-[18px] font-bold text-white"
                aria-expanded={open ? 'true' : 'false'}
                aria-haspopup="true"
                onClick={(e) => {
                    e.preventDefault()
                    e.stopPropagation()
                    setOpen((v) => !v)
                }}
            >
                {(currentLocale || 'es').toUpperCase()}
                <svg className="-mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
            </button>

            <div
                className={`language-dropdown-menu absolute right-0 top-full mt-1 w-[34px] origin-top-right border border-white/90 bg-[#0b6b74]/90 backdrop-blur-lg transition-all duration-200 ease-out ${open ? 'visible scale-100 opacity-100' : 'invisible scale-95 opacity-0'}`}
                role="menu"
                aria-orientation="vertical"
                tabIndex={-1}
            >
                <div className="flex flex-col items-center justify-center gap-0 py-0.5" role="none">
                    {others.map((locale) => (
                        <Link
                            key={locale}
                            href={localePath(locale, currentPath)}
                            className="language-dropdown-link block w-full px-0 py-0.5 text-center text-[15px] font-bold text-white transition-colors duration-150 hover:bg-white hover:text-[#0b6b74]"
                            role="menuitem"
                            tabIndex={-1}
                            onClick={() => setOpen(false)}
                        >
                            {locale.toUpperCase()}
                        </Link>
                    ))}
                </div>
            </div>
        </div>
    )
}
