import { Link } from '@inertiajs/react'

const variants = {
    primary: 'border-cyan-300/75 bg-transparent text-cyan-100 hover:border-cyan-100 hover:bg-cyan-300 hover:text-slate-950 active:border-cyan-50 active:bg-cyan-200 active:text-slate-950',
    secondary: 'border-cyan-300/55 bg-transparent text-cyan-100/92 hover:border-cyan-100 hover:bg-cyan-300 hover:text-slate-950 active:border-cyan-50 active:bg-cyan-200 active:text-slate-950',
    ghost: 'border-cyan-300/35 bg-transparent text-cyan-100/82 hover:border-cyan-100 hover:bg-cyan-300 hover:text-slate-950 active:border-cyan-50 active:bg-cyan-200 active:text-slate-950',
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
    const classes = `inline-flex items-center justify-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-semibold transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-300/35 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950 disabled:cursor-not-allowed disabled:opacity-50 ${variants[variant] ?? variants.secondary} ${className}`

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
