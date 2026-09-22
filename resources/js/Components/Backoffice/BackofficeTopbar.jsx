import { Link } from '@inertiajs/react'
import { FiLogOut, FiMenu } from 'react-icons/fi'

function RoleBadge({ role }) {
    return (
        <span className="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/70">
            {role.replace('_', ' ')}
        </span>
    )
}

export default function BackofficeTopbar({ branding, title, auth, onMenuClick }) {
    const roles = auth?.roles ?? []

    return (
        <header className="sticky top-0 z-30 border-b border-white/10 bg-slate-950/85 backdrop-blur-xl">
            <div className="flex items-center justify-between gap-4 px-4 py-4 md:px-6">
                <div className="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        className="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 text-white/70 transition hover:border-white/20 hover:text-white md:hidden"
                        onClick={onMenuClick}
                    >
                        <FiMenu className="h-5 w-5" />
                    </button>
                    <img src={branding.logo} alt={branding.name} className="h-5 w-auto" />
                    <div className="min-w-0">
                        <p className="truncate text-[11px] uppercase tracking-[0.32em] text-cyan-300/75">Shell React + Inertia</p>
                        <h1 className="truncate text-lg font-semibold text-white">{title}</h1>
                    </div>
                </div>

                <div className="hidden flex-wrap items-center gap-2 lg:flex">
                    {roles.map((role) => (
                        <RoleBadge key={role} role={role} />
                    ))}
                </div>

                <div className="flex items-center gap-3">
                    <div className="hidden text-right sm:block">
                        <p className="text-sm font-semibold text-white">{auth?.user?.name}</p>
                        <p className="text-xs text-white/55">{auth?.user?.email}</p>
                    </div>
                    <Link
                        href="/backoffice/logout"
                        method="post"
                        as="button"
                        className="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 text-white/70 transition hover:border-white/20 hover:text-white"
                    >
                        <FiLogOut className="h-5 w-5" />
                    </Link>
                </div>
            </div>
        </header>
    )
}
