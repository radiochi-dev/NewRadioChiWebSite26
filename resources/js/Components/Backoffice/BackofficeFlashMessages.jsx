import { FiAlertCircle, FiCheckCircle } from 'react-icons/fi'

export default function BackofficeFlashMessages({ flash }) {
    const messages = [
        flash?.success
            ? { key: 'success', icon: FiCheckCircle, className: 'border-emerald-400/30 bg-emerald-400/10 text-emerald-100', text: flash.success }
            : null,
        flash?.error
            ? { key: 'error', icon: FiAlertCircle, className: 'border-rose-400/30 bg-rose-400/10 text-rose-100', text: flash.error }
            : null,
    ].filter(Boolean)

    if (!messages.length) {
        return null
    }

    return (
        <div className="mb-6 space-y-3">
            {messages.map((message) => {
                const Icon = message.icon

                return (
                    <div key={message.key} className={`flex items-start gap-3 rounded-2xl border px-4 py-3 ${message.className}`}>
                        <Icon className="mt-0.5 h-5 w-5 shrink-0" />
                        <p className="text-sm font-medium">{message.text}</p>
                    </div>
                )
            })}
        </div>
    )
}
