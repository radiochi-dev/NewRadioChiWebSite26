import { useMemo } from 'react'

export default function LegacyStarshine() {
    const stars = useMemo(
        () =>
            Array.from({ length: 60 }, (_, i) => {
                const size = i % 2 === 0 ? 'small' : i % 3 === 0 ? 'medium' : 'large'
                return {
                    id: i,
                    size,
                    top: `${Math.random() * 100}%`,
                    left: `${Math.random() * 50}%`,
                    delay: `${Math.random() * 6}s`,
                }
            }),
        [],
    )

    return (
        <div className="legacy-starshine">
            {stars.map((star) => (
                <span
                    key={star.id}
                    className={`legacy-shine ${star.size}`}
                    style={{ top: star.top, left: star.left, animationDelay: star.delay }}
                />
            ))}
        </div>
    )
}
