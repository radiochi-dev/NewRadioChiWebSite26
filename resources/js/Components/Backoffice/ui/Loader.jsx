export default function Loader({ label = 'Cargando shell...' }) {
    return (
        <div className="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm text-white/70">
            <span className="h-2.5 w-2.5 animate-pulse rounded-full bg-cyan-300" />
            <span>{label}</span>
        </div>
    )
}
