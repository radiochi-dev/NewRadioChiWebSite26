import { Link } from '@inertiajs/react'
import { FiChevronRight } from 'react-icons/fi'

export default function BackofficeBreadcrumbs({ items = [], className = 'mb-4 flex flex-wrap items-center gap-2 text-sm text-white/60' }) {
    if (!items.length) {
        return null
    }

    return (
        <nav aria-label="Breadcrumb" className={className}>
            {items.map((item, index) => {
                const isLast = index === items.length - 1

                return (
                    <div key={`${item.label}-${index}`} className="flex items-center gap-2">
                        {item.href && !isLast ? (
                            <Link href={item.href} className="transition hover:text-white">
                                {item.label}
                            </Link>
                        ) : (
                            <span className={isLast ? 'text-white' : ''}>{item.label}</span>
                        )}
                        {!isLast && <FiChevronRight className="h-4 w-4 text-white/30" />}
                    </div>
                )
            })}
        </nav>
    )
}
