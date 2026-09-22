import Button from './Button'

export default function EmptyState({ title, description, ctaLabel, ctaHref }) {
    return (
        <div className="rounded-[28px] border border-dashed border-white/15 bg-white/[0.03] px-6 py-10 text-center">
            <h3 className="text-lg font-semibold text-white">{title}</h3>
            <p className="mx-auto mt-3 max-w-2xl text-sm leading-6 text-white/60">{description}</p>
            {ctaLabel && ctaHref ? (
                <div className="mt-6">
                    <Button href={ctaHref} variant="primary">
                        {ctaLabel}
                    </Button>
                </div>
            ) : null}
        </div>
    )
}
