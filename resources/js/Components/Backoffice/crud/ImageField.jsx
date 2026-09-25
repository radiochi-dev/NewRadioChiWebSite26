import axios from 'axios'
import { useEffect, useMemo, useRef, useState } from 'react'
import { FiImage, FiTrash2, FiUploadCloud } from 'react-icons/fi'
import Button from '../ui/Button'
import Modal from '../ui/Modal'

function normalizePreviewUrl(value) {
    if (typeof value !== 'string' || value.trim() === '') {
        return ''
    }

    if (/^(https?:\/\/|data:|\/)/i.test(value)) {
        return value
    }

    return `/${value.replace(/^\/+/, '')}`
}

function FieldMeta({ error, hint }) {
    return (
        <>
            {error ? <span className="block text-xs text-rose-200">{error}</span> : null}
            {hint ? <span className="block text-xs text-white/45">{hint}</span> : null}
        </>
    )
}

export default function ImageField({ field, form, disabled = false, library = [], uploadUrl = null }) {
    const inputRef = useRef(null)
    const [open, setOpen] = useState(false)
    const [uploading, setUploading] = useState(false)
    const [assets, setAssets] = useState(library)
    const value = typeof form.data[field.key] === 'string' ? form.data[field.key] : ''
    const previewUrl = useMemo(() => normalizePreviewUrl(value), [value])

    useEffect(() => {
        setAssets(library)
    }, [library])

    const removeImage = () => {
        form.setData(field.key, '')
    }

    const selectAsset = (asset) => {
        form.setData(field.key, normalizePreviewUrl(asset.path ?? ''))
        setOpen(false)
    }

    const handleUpload = async (event) => {
        const file = event.target.files?.[0]

        if (!file || !uploadUrl) {
            return
        }

        const payload = new FormData()
        payload.append('image', file)

        setUploading(true)

        try {
            const response = await axios.post(uploadUrl, payload, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })

            const uploadedAsset = response.data?.asset

            if (uploadedAsset) {
                setAssets((current) => [uploadedAsset, ...current.filter((asset) => asset.id !== uploadedAsset.id)])
                form.setData(field.key, normalizePreviewUrl(uploadedAsset.path ?? ''))
            }
        } catch (error) {
            const message = error?.response?.data?.errors?.image?.[0] ?? 'No se pudo subir la imagen.'
            form.setError(field.key, message)
        } finally {
            event.target.value = ''
            setUploading(false)
        }
    }

    return (
        <>
            <div className="space-y-3">
                <div className="flex items-center gap-2">
                    <span className="text-sm font-medium text-white">
                        {field.label}
                        {field.required ? <span className="ml-1 text-rose-300">*</span> : null}
                    </span>
                </div>

                <div className={`overflow-hidden rounded-[24px] border ${form.errors[field.key] ? 'border-rose-400/60' : 'border-white/10'} bg-slate-900/70`}>
                    <div className="relative">
                        {previewUrl ? (
                            <div className="group relative h-56 w-full overflow-hidden bg-slate-950">
                                <img
                                    src={previewUrl}
                                    alt={field.label}
                                    className="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03] group-hover:blur-[2px]"
                                />
                                {!disabled ? (
                                    <>
                                        <div className="absolute inset-0 bg-slate-950/0 transition duration-300 group-hover:bg-slate-950/45" />
                                        <button
                                            type="button"
                                            onClick={removeImage}
                                            className="absolute inset-0 flex items-center justify-center opacity-0 transition duration-300 group-hover:opacity-100"
                                        >
                                            <span className="inline-flex h-14 w-14 items-center justify-center rounded-full border border-white/15 bg-black/55 text-white shadow-[0_18px_40px_rgba(0,0,0,0.28)]">
                                                <FiTrash2 className="h-6 w-6" />
                                            </span>
                                        </button>
                                    </>
                                ) : null}
                            </div>
                        ) : (
                            <div className="flex h-56 flex-col items-center justify-center gap-3 bg-slate-950/65 px-6 text-center">
                                <span className="inline-flex h-14 w-14 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white/70">
                                    <FiImage className="h-6 w-6" />
                                </span>
                                <div>
                                    <p className="text-sm font-semibold text-white">Sin imagen seleccionada</p>
                                    <p className="mt-1 text-xs text-white/45">Elige una imagen existente o sube una nueva desde este campo.</p>
                                </div>
                            </div>
                        )}
                    </div>

                    <div className="flex flex-wrap items-center gap-3 border-t border-white/10 px-4 py-4">
                        <input
                            ref={inputRef}
                            type="file"
                            accept="image/*"
                            className="hidden"
                            onChange={handleUpload}
                            disabled={disabled || uploading || !uploadUrl}
                        />

                        <Button
                            type="button"
                            variant="secondary"
                            disabled={disabled || uploading || !uploadUrl}
                            onClick={() => inputRef.current?.click()}
                        >
                            <FiUploadCloud className="h-4 w-4" />
                            {uploading ? 'Subiendo...' : 'Subir imagen'}
                        </Button>

                        <Button type="button" variant="ghost" disabled={disabled} onClick={() => setOpen(true)}>
                            <FiImage className="h-4 w-4" />
                            Seleccionar de la galeria
                        </Button>
                    </div>
                </div>

                <FieldMeta error={form.errors[field.key]} hint={field.help} />
            </div>

            <Modal
                open={open}
                title={`Galeria de imagenes · ${field.label}`}
                description="Selecciona un asset existente del catalogo multimedia del backoffice."
                onClose={() => setOpen(false)}
            >
                {assets.length ? (
                    <div className="grid max-h-[65vh] gap-4 overflow-y-auto pr-2 sm:grid-cols-2 xl:grid-cols-3">
                        {assets.map((asset) => {
                            const assetPreview = normalizePreviewUrl(asset.previewUrl ?? asset.path ?? '')
                            const selected = normalizePreviewUrl(asset.path ?? '') === previewUrl

                            return (
                                <button
                                    key={asset.id}
                                    type="button"
                                    onClick={() => selectAsset(asset)}
                                    className={`overflow-hidden rounded-2xl border text-left transition ${selected ? 'border-cyan-300/70 bg-cyan-400/10' : 'border-white/10 bg-white/[0.03] hover:border-white/20 hover:bg-white/[0.05]'}`}
                                >
                                    <div className="aspect-[4/3] w-full overflow-hidden bg-slate-950">
                                        <img src={assetPreview} alt={asset.altText || asset.filename} className="h-full w-full object-cover" />
                                    </div>
                                    <div className="space-y-1 px-3 py-3">
                                        <p className="truncate text-sm font-semibold text-white">{asset.filename}</p>
                                        <p className="truncate text-xs text-white/45">{asset.altText || asset.path}</p>
                                    </div>
                                </button>
                            )
                        })}
                    </div>
                ) : (
                    <div className="rounded-2xl border border-dashed border-white/10 bg-white/[0.03] px-4 py-6 text-sm text-white/55">
                        No hay imagenes disponibles en la galeria todavia.
                    </div>
                )}
            </Modal>
        </>
    )
}
