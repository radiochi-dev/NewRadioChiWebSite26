import { Head, router } from '@inertiajs/react'
import { motion } from 'framer-motion'
import { useEffect, useMemo, useState } from 'react'

const API = '/dashboard/api'

function StatCard({ label, value }) {
    return (
        <motion.div initial={{ opacity: 0, y: 14 }} animate={{ opacity: 1, y: 0 }} className="rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur-xl">
            <p className="text-xs uppercase tracking-widest text-white/70">{label}</p>
            <p className="mt-2 text-3xl font-bold text-white">{value}</p>
        </motion.div>
    )
}

function Field({ label, children }) {
    return (
        <label className="flex flex-col gap-1">
            <span className="text-xs font-semibold text-white/70">{label}</span>
            {children}
        </label>
    )
}

function Input(props) {
    return <input {...props} className={`h-10 rounded-lg border border-white/25 bg-white/10 px-3 text-sm text-white outline-none ${props.className ?? ''}`} />
}

function TextArea(props) {
    return <textarea {...props} className={`min-h-20 rounded-lg border border-white/25 bg-white/10 px-3 py-2 text-sm text-white outline-none ${props.className ?? ''}`} />
}

function toText(v) {
    if (v === null || v === undefined) return ''
    if (typeof v === 'object') return JSON.stringify(v)
    return String(v)
}

function DashboardTable({ columns, rows, onEdit, onDelete, onQueue, readOnly = false }) {
    return (
        <div className="overflow-auto rounded-xl border border-white/15">
            <table className="min-w-full text-left text-xs text-white/90">
                <thead>
                    <tr className="border-b border-white/10 bg-white/5">
                        {columns.map((c) => <th key={c.key} className="px-2 py-2 font-semibold">{c.label}</th>)}
                        {!readOnly && <th className="px-2 py-2 font-semibold">Acciones</th>}
                    </tr>
                </thead>
                <tbody>
                    {rows.map((row) => (
                        <tr key={row.id} className="border-b border-white/5">
                            {columns.map((c) => <td key={c.key} className="max-w-[260px] truncate px-2 py-2">{toText(row[c.key])}</td>)}
                            {!readOnly && (
                                <td className="px-2 py-2">
                                    <div className="flex gap-2">
                                        <button onClick={() => onEdit(row)} className="rounded border border-white/30 px-2 py-1">Editar</button>
                                        {onQueue && <button onClick={() => onQueue(row)} className="rounded border border-amber-300/70 px-2 py-1 text-amber-200">Queue</button>}
                                        <button onClick={() => onDelete(row.id)} className="rounded border border-red-300/70 px-2 py-1 text-red-200">Borrar</button>
                                    </div>
                                </td>
                            )}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    )
}

export default function DashboardIndex({ stats }) {
    const tabs = useMemo(() => ['events', 'media', 'pages', 'seo', 'subscribers', 'campaigns', 'logs'], [])
    const [tab, setTab] = useState('events')
    const [busy, setBusy] = useState(false)
    const [msg, setMsg] = useState('')
    const [items, setItems] = useState({ events: [], media: [], pages: [], seo: [], subscribers: [], campaigns: [], logs: [] })
    const [eventForm, setEventForm] = useState({ id: null, slug: '', title: '', excerpt: '', body: '', event_starts_at: '', event_ends_at: '', location: '', external_url: '', is_featured: false, is_published: true, published_at: '' })
    const [mediaForm, setMediaForm] = useState({ id: null, disk: 'public', path: '', filename: '', mime_type: '', size: '', width: '', height: '', alt_text: '', metadata: '{}' })
    const [pageForm, setPageForm] = useState({ id: null, slug: '', template: 'default', is_published: true, published_at: '' })
    const [translationForm, setTranslationForm] = useState({ page_id: '', translation_id: '', locale: 'es', title: '', meta_title: '', meta_description: '', content: '{}' })
    const [seoForm, setSeoForm] = useState({ id: null, entity_type: 'page', entity_id: '', locale: 'es', meta_title: '', meta_description: '', canonical_url: '', open_graph: '{}', twitter_card: '{}', json_ld: '{}' })
    const [subscriberForm, setSubscriberForm] = useState({ id: null, email: '', name: '', is_active: true })
    const [campaignForm, setCampaignForm] = useState({ id: null, name: '', subject: '', html_body: '', scheduled_at: '', status: 'draft' })

    const loadAll = async () => {
        setBusy(true)
        setMsg('')
        try {
            const [events, media, pages, seo, subscribers, campaigns, logs] = await Promise.all([
                window.axios.get(`${API}/events`),
                window.axios.get(`${API}/media`),
                window.axios.get(`${API}/pages`),
                window.axios.get(`${API}/seo-meta`),
                window.axios.get(`${API}/newsletter-subscribers`),
                window.axios.get(`${API}/newsletter-campaigns`),
                window.axios.get(`${API}/newsletter-logs`),
            ])
            setItems({
                events: events.data.data ?? [],
                media: media.data.data ?? [],
                pages: pages.data.data ?? [],
                seo: seo.data.data ?? [],
                subscribers: subscribers.data.data ?? [],
                campaigns: campaigns.data.data ?? [],
                logs: logs.data.data ?? [],
            })
        } catch (e) {
            setMsg('No se pudo cargar el dashboard.')
        } finally {
            setBusy(false)
        }
    }

    useEffect(() => {
        loadAll()
    }, [])

    const safeJson = (value) => {
        if (!value) return {}
        try {
            return JSON.parse(value)
        } catch {
            return {}
        }
    }

    const submit = async (entity) => {
        setBusy(true)
        setMsg('')
        try {
            if (entity === 'events') {
                const payload = { ...eventForm }
                delete payload.id
                if (!payload.published_at) delete payload.published_at
                if (!payload.event_starts_at) delete payload.event_starts_at
                if (!payload.event_ends_at) delete payload.event_ends_at
                if (eventForm.id) await window.axios.put(`${API}/events/${eventForm.id}`, payload)
                else await window.axios.post(`${API}/events`, payload)
            }
            if (entity === 'media') {
                const payload = { ...mediaForm, size: Number(mediaForm.size || 0), width: Number(mediaForm.width || 0), height: Number(mediaForm.height || 0), metadata: safeJson(mediaForm.metadata) }
                delete payload.id
                if (mediaForm.id) await window.axios.put(`${API}/media/${mediaForm.id}`, payload)
                else await window.axios.post(`${API}/media`, payload)
            }
            if (entity === 'pages') {
                const payload = { ...pageForm }
                delete payload.id
                if (!payload.published_at) delete payload.published_at
                if (pageForm.id) await window.axios.put(`${API}/pages/${pageForm.id}`, payload)
                else await window.axios.post(`${API}/pages`, payload)
            }
            if (entity === 'seo') {
                const payload = { ...seoForm, entity_id: Number(seoForm.entity_id || 0), open_graph: safeJson(seoForm.open_graph), twitter_card: safeJson(seoForm.twitter_card), json_ld: safeJson(seoForm.json_ld) }
                delete payload.id
                if (seoForm.id) await window.axios.put(`${API}/seo-meta/${seoForm.id}`, payload)
                else await window.axios.post(`${API}/seo-meta`, payload)
            }
            if (entity === 'subscribers') {
                const payload = { ...subscriberForm }
                delete payload.id
                if (subscriberForm.id) await window.axios.put(`${API}/newsletter-subscribers/${subscriberForm.id}`, payload)
                else await window.axios.post(`${API}/newsletter-subscribers`, payload)
            }
            if (entity === 'campaigns') {
                const payload = { ...campaignForm }
                delete payload.id
                if (!payload.scheduled_at) delete payload.scheduled_at
                if (campaignForm.id) await window.axios.put(`${API}/newsletter-campaigns/${campaignForm.id}`, payload)
                else await window.axios.post(`${API}/newsletter-campaigns`, payload)
            }
            if (entity === 'translations') {
                const payload = {
                    locale: translationForm.locale,
                    title: translationForm.title,
                    meta_title: translationForm.meta_title || null,
                    meta_description: translationForm.meta_description || null,
                    content: safeJson(translationForm.content),
                }
                if (translationForm.translation_id) await window.axios.put(`${API}/pages/${translationForm.page_id}/translations/${translationForm.translation_id}`, payload)
                else await window.axios.post(`${API}/pages/${translationForm.page_id}/translations`, payload)
            }
            await loadAll()
            setMsg('Guardado correctamente.')
        } catch (e) {
            setMsg('Error al guardar datos. Revisa campos obligatorios.')
        } finally {
            setBusy(false)
        }
    }

    const remove = async (entity, id) => {
        setBusy(true)
        setMsg('')
        try {
            await window.axios.delete(`${API}/${entity}/${id}`)
            await loadAll()
            setMsg('Elemento eliminado.')
        } catch {
            setMsg('No se pudo eliminar.')
        } finally {
            setBusy(false)
        }
    }

    const queueCampaign = async (id) => {
        setBusy(true)
        setMsg('')
        try {
            await window.axios.post(`${API}/newsletter-campaigns/${id}/queue`)
            await loadAll()
            setMsg('Campaña en cola.')
        } catch {
            setMsg('No se pudo encolar la campaña.')
        } finally {
            setBusy(false)
        }
    }

    return (
        <>
            <Head title="Dashboard Admin" />
            <div className="min-h-screen bg-[radial-gradient(circle_at_20%_0%,#0ea5b6_0%,#0b1f2d_40%,#07131c_100%)] p-5">
                <div className="mx-auto max-w-7xl">
                    <div className="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h1 className="text-3xl font-black text-white">Dashboard SuperAdmin</h1>
                            <p className="text-sm text-white/70">CRUD visual completo con endpoints admin ya disponibles.</p>
                        </div>
                        <div className="flex gap-2">
                            <button onClick={loadAll} className="rounded-xl border border-white/30 px-4 py-2 text-sm font-bold text-white transition hover:bg-white hover:text-[#072030]">Refrescar</button>
                            <button onClick={() => router.post('/logout')} className="rounded-xl border border-white/30 px-4 py-2 text-sm font-bold text-white transition hover:bg-white hover:text-[#072030]">Cerrar sesión</button>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
                        <StatCard label="Eventos" value={stats.events} />
                        <StatCard label="Páginas" value={stats.pages} />
                        <StatCard label="Media" value={stats.media} />
                        <StatCard label="SEO" value={stats.seo} />
                        <StatCard label="Subscriptores" value={stats.subscribers} />
                        <StatCard label="Campañas" value={stats.campaigns} />
                    </div>

                    <div className="mt-4 flex flex-wrap gap-2">
                        {tabs.map((t) => (
                            <button key={t} onClick={() => setTab(t)} className={`rounded-full border px-3 py-1 text-xs font-bold uppercase ${tab === t ? 'border-white bg-white text-[#072030]' : 'border-white/40 text-white'}`}>
                                {t}
                            </button>
                        ))}
                    </div>

                    {msg && <div className="mt-3 rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-sm text-white">{msg}</div>}
                    {busy && <div className="mt-2 text-xs text-white/70">Procesando...</div>}

                    {tab === 'events' && (
                        <div className="mt-4 grid gap-4 lg:grid-cols-2">
                            <div className="rounded-2xl border border-white/20 bg-[#0d2530]/70 p-4">
                                <h3 className="mb-3 text-lg font-bold text-white">Evento</h3>
                                <div className="grid gap-2">
                                    <Field label="Slug"><Input value={eventForm.slug} onChange={(e) => setEventForm((v) => ({ ...v, slug: e.target.value }))} /></Field>
                                    <Field label="Título"><Input value={eventForm.title} onChange={(e) => setEventForm((v) => ({ ...v, title: e.target.value }))} /></Field>
                                    <Field label="Excerpt"><TextArea value={eventForm.excerpt} onChange={(e) => setEventForm((v) => ({ ...v, excerpt: e.target.value }))} /></Field>
                                    <Field label="Body"><TextArea value={eventForm.body} onChange={(e) => setEventForm((v) => ({ ...v, body: e.target.value }))} /></Field>
                                    <Field label="Inicio"><Input type="datetime-local" value={eventForm.event_starts_at} onChange={(e) => setEventForm((v) => ({ ...v, event_starts_at: e.target.value }))} /></Field>
                                    <Field label="Fin"><Input type="datetime-local" value={eventForm.event_ends_at} onChange={(e) => setEventForm((v) => ({ ...v, event_ends_at: e.target.value }))} /></Field>
                                    <Field label="Location"><Input value={eventForm.location} onChange={(e) => setEventForm((v) => ({ ...v, location: e.target.value }))} /></Field>
                                    <Field label="URL externa"><Input value={eventForm.external_url} onChange={(e) => setEventForm((v) => ({ ...v, external_url: e.target.value }))} /></Field>
                                </div>
                                <div className="mt-3 flex gap-2">
                                    <button onClick={() => submit('events')} className="rounded-lg border border-white/30 px-3 py-2 text-sm font-bold text-white">Guardar</button>
                                    <button onClick={() => setEventForm({ id: null, slug: '', title: '', excerpt: '', body: '', event_starts_at: '', event_ends_at: '', location: '', external_url: '', is_featured: false, is_published: true, published_at: '' })} className="rounded-lg border border-white/30 px-3 py-2 text-sm text-white/80">Nuevo</button>
                                </div>
                            </div>
                            <DashboardTable columns={[{ key: 'id', label: 'ID' }, { key: 'slug', label: 'Slug' }, { key: 'title', label: 'Título' }, { key: 'location', label: 'Location' }]} rows={items.events} onEdit={setEventForm} onDelete={(id) => remove('events', id)} />
                        </div>
                    )}

                    {tab === 'media' && (
                        <div className="mt-4 grid gap-4 lg:grid-cols-2">
                            <div className="rounded-2xl border border-white/20 bg-[#0d2530]/70 p-4">
                                <h3 className="mb-3 text-lg font-bold text-white">Media Asset</h3>
                                <div className="grid gap-2">
                                    <Field label="Disk"><Input value={mediaForm.disk} onChange={(e) => setMediaForm((v) => ({ ...v, disk: e.target.value }))} /></Field>
                                    <Field label="Path"><Input value={mediaForm.path} onChange={(e) => setMediaForm((v) => ({ ...v, path: e.target.value }))} /></Field>
                                    <Field label="Filename"><Input value={mediaForm.filename} onChange={(e) => setMediaForm((v) => ({ ...v, filename: e.target.value }))} /></Field>
                                    <Field label="MIME"><Input value={mediaForm.mime_type} onChange={(e) => setMediaForm((v) => ({ ...v, mime_type: e.target.value }))} /></Field>
                                    <Field label="Alt"><Input value={mediaForm.alt_text} onChange={(e) => setMediaForm((v) => ({ ...v, alt_text: e.target.value }))} /></Field>
                                    <Field label="Metadata JSON"><TextArea value={mediaForm.metadata} onChange={(e) => setMediaForm((v) => ({ ...v, metadata: e.target.value }))} /></Field>
                                </div>
                                <div className="mt-3 flex gap-2">
                                    <button onClick={() => submit('media')} className="rounded-lg border border-white/30 px-3 py-2 text-sm font-bold text-white">Guardar</button>
                                    <button onClick={() => setMediaForm({ id: null, disk: 'public', path: '', filename: '', mime_type: '', size: '', width: '', height: '', alt_text: '', metadata: '{}' })} className="rounded-lg border border-white/30 px-3 py-2 text-sm text-white/80">Nuevo</button>
                                </div>
                            </div>
                            <DashboardTable columns={[{ key: 'id', label: 'ID' }, { key: 'disk', label: 'Disk' }, { key: 'path', label: 'Path' }, { key: 'filename', label: 'Filename' }]} rows={items.media} onEdit={(row) => setMediaForm({ ...row, metadata: JSON.stringify(row.metadata ?? {}) })} onDelete={(id) => remove('media', id)} />
                        </div>
                    )}

                    {tab === 'pages' && (
                        <div className="mt-4 grid gap-4 lg:grid-cols-3">
                            <div className="rounded-2xl border border-white/20 bg-[#0d2530]/70 p-4">
                                <h3 className="mb-3 text-lg font-bold text-white">Página</h3>
                                <div className="grid gap-2">
                                    <Field label="Slug"><Input value={pageForm.slug} onChange={(e) => setPageForm((v) => ({ ...v, slug: e.target.value }))} /></Field>
                                    <Field label="Template"><Input value={pageForm.template} onChange={(e) => setPageForm((v) => ({ ...v, template: e.target.value }))} /></Field>
                                </div>
                                <div className="mt-3 flex gap-2">
                                    <button onClick={() => submit('pages')} className="rounded-lg border border-white/30 px-3 py-2 text-sm font-bold text-white">Guardar</button>
                                </div>
                            </div>
                            <div className="rounded-2xl border border-white/20 bg-[#0d2530]/70 p-4">
                                <h3 className="mb-3 text-lg font-bold text-white">Traducción</h3>
                                <div className="grid gap-2">
                                    <Field label="Page ID"><Input value={translationForm.page_id} onChange={(e) => setTranslationForm((v) => ({ ...v, page_id: e.target.value }))} /></Field>
                                    <Field label="Translation ID"><Input value={translationForm.translation_id} onChange={(e) => setTranslationForm((v) => ({ ...v, translation_id: e.target.value }))} /></Field>
                                    <Field label="Locale"><Input value={translationForm.locale} onChange={(e) => setTranslationForm((v) => ({ ...v, locale: e.target.value }))} /></Field>
                                    <Field label="Title"><Input value={translationForm.title} onChange={(e) => setTranslationForm((v) => ({ ...v, title: e.target.value }))} /></Field>
                                    <Field label="Meta title"><Input value={translationForm.meta_title} onChange={(e) => setTranslationForm((v) => ({ ...v, meta_title: e.target.value }))} /></Field>
                                    <Field label="Meta description"><TextArea value={translationForm.meta_description} onChange={(e) => setTranslationForm((v) => ({ ...v, meta_description: e.target.value }))} /></Field>
                                    <Field label="Content JSON"><TextArea value={translationForm.content} onChange={(e) => setTranslationForm((v) => ({ ...v, content: e.target.value }))} /></Field>
                                </div>
                                <div className="mt-3 flex gap-2">
                                    <button onClick={() => submit('translations')} className="rounded-lg border border-white/30 px-3 py-2 text-sm font-bold text-white">Guardar traducción</button>
                                </div>
                            </div>
                            <DashboardTable columns={[{ key: 'id', label: 'ID' }, { key: 'slug', label: 'Slug' }, { key: 'template', label: 'Template' }, { key: 'is_published', label: 'Published' }]} rows={items.pages} onEdit={setPageForm} onDelete={(id) => remove('pages', id)} />
                        </div>
                    )}

                    {tab === 'seo' && (
                        <div className="mt-4 grid gap-4 lg:grid-cols-2">
                            <div className="rounded-2xl border border-white/20 bg-[#0d2530]/70 p-4">
                                <h3 className="mb-3 text-lg font-bold text-white">SEO</h3>
                                <div className="grid gap-2">
                                    <Field label="Entity type"><Input value={seoForm.entity_type} onChange={(e) => setSeoForm((v) => ({ ...v, entity_type: e.target.value }))} /></Field>
                                    <Field label="Entity id"><Input value={seoForm.entity_id} onChange={(e) => setSeoForm((v) => ({ ...v, entity_id: e.target.value }))} /></Field>
                                    <Field label="Locale"><Input value={seoForm.locale} onChange={(e) => setSeoForm((v) => ({ ...v, locale: e.target.value }))} /></Field>
                                    <Field label="Meta title"><Input value={seoForm.meta_title} onChange={(e) => setSeoForm((v) => ({ ...v, meta_title: e.target.value }))} /></Field>
                                    <Field label="Meta description"><TextArea value={seoForm.meta_description} onChange={(e) => setSeoForm((v) => ({ ...v, meta_description: e.target.value }))} /></Field>
                                    <Field label="Canonical"><Input value={seoForm.canonical_url} onChange={(e) => setSeoForm((v) => ({ ...v, canonical_url: e.target.value }))} /></Field>
                                    <Field label="Open Graph JSON"><TextArea value={seoForm.open_graph} onChange={(e) => setSeoForm((v) => ({ ...v, open_graph: e.target.value }))} /></Field>
                                </div>
                                <div className="mt-3 flex gap-2">
                                    <button onClick={() => submit('seo')} className="rounded-lg border border-white/30 px-3 py-2 text-sm font-bold text-white">Guardar</button>
                                </div>
                            </div>
                            <DashboardTable columns={[{ key: 'id', label: 'ID' }, { key: 'entity_type', label: 'Type' }, { key: 'entity_id', label: 'Entity' }, { key: 'locale', label: 'Locale' }]} rows={items.seo} onEdit={(row) => setSeoForm({ ...row, open_graph: JSON.stringify(row.open_graph ?? {}), twitter_card: JSON.stringify(row.twitter_card ?? {}), json_ld: JSON.stringify(row.json_ld ?? {}) })} onDelete={(id) => remove('seo-meta', id)} />
                        </div>
                    )}

                    {tab === 'subscribers' && (
                        <div className="mt-4 grid gap-4 lg:grid-cols-2">
                            <div className="rounded-2xl border border-white/20 bg-[#0d2530]/70 p-4">
                                <h3 className="mb-3 text-lg font-bold text-white">Subscriber</h3>
                                <div className="grid gap-2">
                                    <Field label="Email"><Input value={subscriberForm.email} onChange={(e) => setSubscriberForm((v) => ({ ...v, email: e.target.value }))} /></Field>
                                    <Field label="Nombre"><Input value={subscriberForm.name} onChange={(e) => setSubscriberForm((v) => ({ ...v, name: e.target.value }))} /></Field>
                                </div>
                                <div className="mt-3 flex gap-2">
                                    <button onClick={() => submit('subscribers')} className="rounded-lg border border-white/30 px-3 py-2 text-sm font-bold text-white">Guardar</button>
                                </div>
                            </div>
                            <DashboardTable columns={[{ key: 'id', label: 'ID' }, { key: 'email', label: 'Email' }, { key: 'name', label: 'Nombre' }, { key: 'is_active', label: 'Activo' }]} rows={items.subscribers} onEdit={setSubscriberForm} onDelete={(id) => remove('newsletter-subscribers', id)} />
                        </div>
                    )}

                    {tab === 'campaigns' && (
                        <div className="mt-4 grid gap-4 lg:grid-cols-2">
                            <div className="rounded-2xl border border-white/20 bg-[#0d2530]/70 p-4">
                                <h3 className="mb-3 text-lg font-bold text-white">Campaña</h3>
                                <div className="grid gap-2">
                                    <Field label="Nombre"><Input value={campaignForm.name} onChange={(e) => setCampaignForm((v) => ({ ...v, name: e.target.value }))} /></Field>
                                    <Field label="Subject"><Input value={campaignForm.subject} onChange={(e) => setCampaignForm((v) => ({ ...v, subject: e.target.value }))} /></Field>
                                    <Field label="HTML Body"><TextArea value={campaignForm.html_body} onChange={(e) => setCampaignForm((v) => ({ ...v, html_body: e.target.value }))} /></Field>
                                    <Field label="Programada"><Input type="datetime-local" value={campaignForm.scheduled_at} onChange={(e) => setCampaignForm((v) => ({ ...v, scheduled_at: e.target.value }))} /></Field>
                                    <Field label="Status"><Input value={campaignForm.status} onChange={(e) => setCampaignForm((v) => ({ ...v, status: e.target.value }))} /></Field>
                                </div>
                                <div className="mt-3 flex gap-2">
                                    <button onClick={() => submit('campaigns')} className="rounded-lg border border-white/30 px-3 py-2 text-sm font-bold text-white">Guardar</button>
                                </div>
                            </div>
                            <DashboardTable columns={[{ key: 'id', label: 'ID' }, { key: 'name', label: 'Name' }, { key: 'subject', label: 'Subject' }, { key: 'status', label: 'Status' }]} rows={items.campaigns} onEdit={setCampaignForm} onDelete={(id) => remove('newsletter-campaigns', id)} onQueue={(row) => queueCampaign(row.id)} />
                        </div>
                    )}

                    {tab === 'logs' && (
                        <div className="mt-4">
                            <DashboardTable columns={[{ key: 'id', label: 'ID' }, { key: 'campaign_id', label: 'Campaign' }, { key: 'subscriber_id', label: 'Subscriber' }, { key: 'status', label: 'Status' }, { key: 'processed_at', label: 'Fecha' }]} rows={items.logs} readOnly />
                        </div>
                    )}
                </div>
            </div>
        </>
    )
}
