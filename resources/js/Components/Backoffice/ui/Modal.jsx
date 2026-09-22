import Button from './Button'

export default function Modal({ open, title, description, onClose, children }) {
    if (!open) {
        return null
    }

    return (
        <div className="fixed inset-0 z-[80] flex items-center justify-center bg-black/70 px-4 backdrop-blur-sm">
            <div className="w-full max-w-2xl rounded-[28px] border border-white/10 bg-slate-950 p-6 shadow-[0_30px_80px_rgba(0,0,0,0.45)]">
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <h3 className="text-xl font-semibold text-white">{title}</h3>
                        {description ? <p className="mt-2 text-sm text-white/60">{description}</p> : null}
                    </div>
                    <Button variant="ghost" onClick={onClose}>
                        Cerrar
                    </Button>
                </div>
                <div className="mt-6">{children}</div>
            </div>
        </div>
    )
}
