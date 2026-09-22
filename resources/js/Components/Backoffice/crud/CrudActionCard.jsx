import { useForm } from '@inertiajs/react'
import Button from '../ui/Button'
import Input from '../ui/Input'

const tones = {
    cyan: 'border-cyan-400/20 bg-cyan-400/10',
    emerald: 'border-emerald-400/20 bg-emerald-400/10',
    fuchsia: 'border-fuchsia-400/20 bg-fuchsia-400/10',
    amber: 'border-amber-400/20 bg-amber-400/10',
    rose: 'border-rose-400/20 bg-rose-400/10',
}

export default function CrudActionCard({ action }) {
    const form = useForm({
        confirmation: '',
        record: action.record ?? '',
    })

    const submit = (event) => {
        event.preventDefault()

        form.post(action.endpoint, {
            preserveScroll: true,
        })
    }

    return (
        <form onSubmit={submit} className={`rounded-2xl border p-4 ${tones[action.tone] ?? tones.cyan}`}>
            <div className="space-y-4">
                <div>
                    <p className="text-sm font-semibold text-white">{action.label}</p>
                    <p className="mt-2 text-sm leading-6 text-white/65">{action.description}</p>
                </div>

                {action.requires_confirmation ? (
                    <Input
                        label={`Escribe ${action.confirmation_phrase}`}
                        value={form.data.confirmation}
                        onChange={(event) => form.setData('confirmation', event.target.value)}
                        error={form.errors.confirmation}
                        hint="Patron de confirmacion manual antes de ejecutar acciones sensibles."
                    />
                ) : null}

                <div className="flex flex-wrap justify-end gap-3">
                    <Button type="submit" variant={action.tone === 'rose' ? 'ghost' : 'secondary'} disabled={form.processing}>
                        {form.processing ? 'Validando...' : action.label}
                    </Button>
                </div>
            </div>
        </form>
    )
}
