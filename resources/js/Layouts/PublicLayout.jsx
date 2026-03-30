import { Head } from '@inertiajs/react'
import LanguageSwitcher from '../Components/LanguageSwitcher'

export default function PublicLayout({ title, locale, locales, currentPath, menu, seo, hideNav = false, children }) {
    return (
        <>
            <Head title={title}>
                <link rel="stylesheet" href="/assets/fonts/Inter/style.css" />
                <meta name="description" content={seo.description} />
                <link rel="canonical" href={seo.canonical} />
                <meta property="og:type" content={seo.ogType} />
                <meta property="og:site_name" content="RadioChi" />
                <meta property="og:title" content={title} />
                <meta property="og:description" content={seo.description} />
                <meta property="og:url" content={seo.canonical} />
                <meta property="og:image" content={seo.ogImage} />
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={title} />
                <meta name="twitter:description" content={seo.description} />
                <meta name="twitter:image" content={seo.ogImage} />
                {Object.entries(seo.alternates).map(([alternateLocale, href]) => (
                    <link key={alternateLocale} rel="alternate" hrefLang={alternateLocale} href={href} />
                ))}
                <link rel="alternate" hrefLang="x-default" href={seo.xDefault} />
                <script type="application/ld+json">{JSON.stringify(seo.jsonLd)}</script>
            </Head>
            {!hideNav && (
                <nav className="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-black/80 backdrop-blur">
                    <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
                        <a href="#home" className="text-lg font-semibold text-white">
                            RadioChi
                        </a>
                        <div className="hidden items-center gap-6 md:flex">
                            <a href="#home" className="text-sm text-white/90 transition hover:text-white">
                                {menu.home}
                            </a>
                            <a href="#about" className="text-sm text-white/90 transition hover:text-white">
                                {menu.about}
                            </a>
                            <a href="#music" className="text-sm text-white/90 transition hover:text-white">
                                {menu.music}
                            </a>
                            <a href="#events" className="text-sm text-white/90 transition hover:text-white">
                                {menu.events}
                            </a>
                            <a href="#media" className="text-sm text-white/90 transition hover:text-white">
                                {menu.media}
                            </a>
                            <a href="#contact" className="text-sm text-white/90 transition hover:text-white">
                                {menu.contact}
                            </a>
                        </div>
                        <LanguageSwitcher currentLocale={locale} locales={locales} currentPath={currentPath} />
                    </div>
                </nav>
            )}
            {children}
        </>
    )
}
