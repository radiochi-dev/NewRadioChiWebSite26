import { Link, usePage } from '@inertiajs/react'
import {
    FiCalendar,
    FiDownload,
    FiFileText,
    FiGrid,
    FiHome,
    FiImage,
    FiLayout,
    FiMail,
    FiMusic,
    FiRepeat,
    FiSearch,
    FiSend,
    FiSettings,
    FiShare2,
    FiShield,
    FiTerminal,
    FiUsers,
    FiX,
} from 'react-icons/fi'

const iconMap = {
    calendar: FiCalendar,
    download: FiDownload,
    file: FiFileText,
    home: FiHome,
    image: FiImage,
    layout: FiLayout,
    mail: FiMail,
    music: FiMusic,
    panel: FiGrid,
    repeat: FiRepeat,
    search: FiSearch,
    send: FiSend,
    settings: FiSettings,
    share: FiShare2,
    shield: FiShield,
    terminal: FiTerminal,
    users: FiUsers,
}

function normalizePath(value) {
    if (typeof value !== 'string' || value.trim() === '') {
        return '/'
    }

    const [path] = value.split(/[?#]/)
    const normalized = path.replace(/\/+$/, '')

    return normalized === '' ? '/' : normalized
}

function isItemActive(currentUrl, itemHref) {
    const current = normalizePath(currentUrl)
    const target = normalizePath(itemHref)

    if (target === '/backoffice') {
        return current === target
    }

    return current === target || current.startsWith(`${target}/`)
}

function NavItem({ item, active, onNavigate }) {
    const Icon = iconMap[item.icon] ?? FiGrid
    const classes = active
        ? 'border-cyan-400/40 bg-cyan-400/10 text-white'
        : 'border-transparent text-white/70 hover:border-white/10 hover:bg-white/5 hover:text-white'
    const descriptionClasses = active
        ? 'mt-1 max-h-16 translate-y-0 opacity-100'
        : 'mt-0 max-h-0 translate-y-1 opacity-0 group-hover:mt-1 group-hover:max-h-16 group-hover:translate-y-0 group-hover:opacity-100'

    return (
        <Link
            href={item.href}
            className={`group flex items-start gap-3 rounded-2xl border px-3 py-3 transition ${classes}`}
            onClick={onNavigate}
        >
            <Icon className="mt-0.5 h-4 w-4 shrink-0" />
            <span className="min-w-0">
                <span className="block text-sm font-semibold">{item.label}</span>
                <span className={`block overflow-hidden text-xs text-white/45 transition-all duration-300 ease-out ${descriptionClasses}`}>
                    {item.description}
                </span>
            </span>
        </Link>
    )
}

export default function BackofficeSidebar({ branding, navigation = [], isOpen = false, onClose }) {
    const { url } = usePage()
    const pinnedGroups = navigation.filter((group) => group.items?.some((item) => item.slug === 'settings'))
    const scrollableGroups = navigation.filter((group) => !group.items?.some((item) => item.slug === 'settings'))

    return (
        <>
            <div
                className={`fixed inset-0 z-40 bg-black/70 backdrop-blur-sm transition md:hidden ${isOpen ? 'opacity-100' : 'pointer-events-none opacity-0'}`}
                onClick={onClose}
            />
            <aside
                className={`fixed inset-y-0 left-0 z-50 w-80 max-w-[88vw] border-r border-white/10 bg-slate-950/95 backdrop-blur-xl transition md:static md:z-auto md:w-80 md:translate-x-0 ${isOpen ? 'translate-x-0' : '-translate-x-full'}`}
            >
                <div className="flex h-full flex-col">
                    <div className="flex h-24 items-center justify-between border-b border-white/10 px-5">
                        <div className="min-w-0">
                            <img
                                src={branding?.logo}
                                alt={branding?.name ?? 'RadioChi Backoffice'}
                                className="h-12 w-auto max-w-[72px] object-contain"
                            />
                        </div>
                        <button
                            type="button"
                            className="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-white/10 text-white/70 transition hover:border-white/20 hover:text-white md:hidden"
                            onClick={onClose}
                        >
                            <FiX className="h-5 w-5" />
                        </button>
                    </div>

                    <div className="backoffice-scrollbar-hidden min-h-0 flex-1 overflow-y-auto px-4 py-4">
                        <div className="space-y-6">
                            {scrollableGroups.map((group) => (
                                <section key={group.label} className="space-y-3">
                                    <p className="px-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-white/35">
                                        {group.label}
                                    </p>
                                    <div className="space-y-2">
                                        {group.items.map((item) => (
                                            <NavItem
                                                key={item.slug}
                                                item={item}
                                                active={isItemActive(url, item.href)}
                                                onNavigate={onClose}
                                            />
                                        ))}
                                    </div>
                                </section>
                            ))}
                        </div>
                    </div>

                    {pinnedGroups.length ? (
                        <div className="border-t border-white/10 px-4 py-4">
                            <div className="space-y-6">
                                {pinnedGroups.map((group) => (
                                    <section key={group.label} className="space-y-3">
                                        <p className="px-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-white/35">
                                            {group.label}
                                        </p>
                                        <div className="space-y-2">
                                            {group.items.map((item) => (
                                                <NavItem
                                                    key={item.slug}
                                                    item={item}
                                                    active={isItemActive(url, item.href)}
                                                    onNavigate={onClose}
                                                />
                                            ))}
                                        </div>
                                    </section>
                                ))}
                            </div>
                        </div>
                    ) : null}
                </div>
            </aside>
        </>
    )
}
