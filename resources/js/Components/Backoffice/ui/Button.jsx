import { Link } from '@inertiajs/react'

const variants = {
    primary: 'border-cyan-400/40 bg-gradient-to-r from-fuchsia-500 via-violet-500 to-cyan-400 text-white shadow-[0_12px_40px_rgba(34,211,238,0.18)]',
    secondary: 'border-white/10 bg-white/10 text-white hover:bg-white/15',
    ghost: 'border-white/10 bg-transparent text-white/80 hover:bg-white/5 hover:text-white',
}

export default function Button({
    children,
    href,
    method = 'get',
    variant = 'secondary',
    className = '',
    disabled = false,
    external = false,
    type = 'button',
    ...props
}) {
    const classes = `inline-flex items-center justify-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-50 ${variants[variant] ?? variants.secondary} ${className}`

    if (href && external) {
        return (
            <a href={href} className={classes} {...props}>
                {children}
            </a>
        )
    }

    if (href) {
        return (
            <Link href={href} method={method} as="button" className={classes} disabled={disabled} {...props}>
                {children}
            </Link>
        )
    }

    return (
        <button type={type} className={classes} disabled={disabled} {...props}>
            {children}
        </button>
    )
}
