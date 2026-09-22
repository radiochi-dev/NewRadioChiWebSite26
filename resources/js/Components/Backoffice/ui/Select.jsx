export default function Select({ label, options = [], hint, error, required = false, className = '', ...props }) {
    return (
        <label className={`block space-y-2 ${className}`}>
            <span className="text-sm font-medium text-white">
                {label}
                {required ? <span className="ml-1 text-rose-300">*</span> : null}
            </span>
            <select
                className={`w-full rounded-2xl border bg-slate-900/80 px-4 py-3 text-sm text-white outline-none transition disabled:cursor-not-allowed disabled:opacity-60 ${
                    error ? 'border-rose-400/60' : 'border-white/10 focus:border-cyan-300/60'
                }`}
                {...props}
            >
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
            {error ? <span className="block text-xs text-rose-200">{error}</span> : null}
            {hint ? <span className="block text-xs text-white/45">{hint}</span> : null}
        </label>
    )
}
