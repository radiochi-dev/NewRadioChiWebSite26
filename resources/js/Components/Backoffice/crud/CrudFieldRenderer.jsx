import ImageField from './ImageField'
import Input from '../ui/Input'
import Select from '../ui/Select'
import Textarea from '../ui/Textarea'

function PanelField({ field }) {
    return (
        <div className="rounded-2xl border border-white/10 bg-black/20 p-4">
            <p className="text-sm font-semibold text-white">{field.label}</p>
            <p className="mt-2 text-sm leading-6 text-white/60">{field.help}</p>
        </div>
    )
}

export default function CrudFieldRenderer({ field, form, disabled = false, mediaLibrary = [], mediaUploadUrl = null }) {
    const common = {
        label: field.label,
        hint: field.help,
        required: field.required,
        disabled: disabled || field.disabled,
        error: form.errors[field.key],
    }

    if (field.surface !== 'input') {
        return <PanelField field={field} />
    }

    if (field.type === 'select') {
        return (
            <Select
                {...common}
                value={form.data[field.key] ?? ''}
                onChange={(event) => form.setData(field.key, event.target.value)}
                options={[{ value: '', label: 'Selecciona una opcion' }, ...(field.options ?? [])]}
            />
        )
    }

    if (field.type === 'textarea' || field.type === 'json') {
        return (
            <Textarea
                {...common}
                value={form.data[field.key] ?? ''}
                onChange={(event) => form.setData(field.key, event.target.value)}
                className={field.type === 'json' ? 'xl:col-span-2' : ''}
                placeholder={field.type === 'json' ? '{\n  "key": "value"\n}' : ''}
            />
        )
    }

    if (field.type === 'toggle') {
        return (
            <label className="flex items-center justify-between rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                <div className="pr-6">
                    <p className="text-sm font-semibold text-white">{field.label}</p>
                    <p className="mt-1 text-xs text-white/45">{field.help}</p>
                    {form.errors[field.key] ? <p className="mt-2 text-xs text-rose-200">{form.errors[field.key]}</p> : null}
                </div>
                <input
                    type="checkbox"
                    checked={Boolean(form.data[field.key])}
                    onChange={(event) => form.setData(field.key, event.target.checked)}
                    disabled={disabled || field.disabled}
                    className="h-5 w-5 rounded border-white/20 bg-slate-950"
                />
            </label>
        )
    }

    if (field.type === 'file') {
        return (
            <label className="block space-y-2 rounded-2xl border border-dashed border-white/15 bg-black/20 px-4 py-4">
                <span className="text-sm font-medium text-white">{field.label}</span>
                <input
                    type="file"
                    accept={field.accept ?? undefined}
                    disabled={disabled || field.disabled}
                    onChange={(event) => form.setData(field.key, event.target.files?.[0] ?? null)}
                    className="block w-full text-sm text-white/70 file:mr-4 file:rounded-2xl file:border-0 file:bg-white/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white"
                />
                <span className="block text-xs text-white/45">{field.help}</span>
                {form.errors[field.key] ? <span className="block text-xs text-rose-200">{form.errors[field.key]}</span> : null}
            </label>
        )
    }

    if (field.type === 'image') {
        return <ImageField field={field} form={form} disabled={disabled} library={mediaLibrary} uploadUrl={mediaUploadUrl} />
    }

    return (
        <Input
            {...common}
            type={field.type === 'email' ? 'email' : field.type === 'url' ? 'url' : field.type === 'number' ? 'number' : field.type === 'datetime' ? 'datetime-local' : 'text'}
            value={form.data[field.key] ?? ''}
            onChange={(event) => form.setData(field.key, event.target.value)}
        />
    )
}
