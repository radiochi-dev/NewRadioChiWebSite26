export default function ProgressBar({ sections, activeSection, onSelect }) {
    const activeIndex = sections.indexOf(activeSection)
    const progress = ((activeIndex + 1) / sections.length) * 100

    return (
        <div className="fixed left-3 top-1/2 z-40 hidden h-64 w-3 -translate-y-1/2 md:block">
            <div className="absolute left-1/2 top-0 h-full w-px -translate-x-1/2 bg-white/20" />
            <div className="absolute left-1/2 top-0 w-px -translate-x-1/2 bg-white transition-all duration-300" style={{ height: `${progress}%` }} />
            {sections.map((section, index) => (
                <button
                    key={section}
                    type="button"
                    onClick={() => onSelect(section)}
                    className={`absolute left-1/2 h-2.5 w-2.5 -translate-x-1/2 rounded-full transition ${
                        section === activeSection ? 'bg-white' : 'bg-white/40 hover:bg-white/70'
                    }`}
                    style={{ top: `${(index / (sections.length - 1)) * 100}%` }}
                />
            ))}
        </div>
    )
}
