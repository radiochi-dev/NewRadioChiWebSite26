import { useEffect, useMemo } from 'react'
import { EditorContent, useEditor } from '@tiptap/react'
import StarterKit from '@tiptap/starter-kit'
import Link from '@tiptap/extension-link'
import Underline from '@tiptap/extension-underline'
import Highlight from '@tiptap/extension-highlight'
import TextAlign from '@tiptap/extension-text-align'
import Placeholder from '@tiptap/extension-placeholder'
import {
    FiAlignCenter,
    FiAlignLeft,
    FiAlignRight,
    FiBold,
    FiCode,
    FiCornerDownLeft,
    FiEdit3,
    FiItalic,
    FiLink,
    FiList,
    FiMenu,
    FiMinus,
    FiRotateCcw,
    FiRotateCw,
    FiTrash2,
    FiType,
    FiUnderline,
} from 'react-icons/fi'

function ToolbarButton({ label, icon: Icon, active = false, disabled = false, onClick }) {
    return (
        <button
            type="button"
            onClick={onClick}
            disabled={disabled}
            title={label}
            aria-label={label}
            className={`inline-flex h-10 w-10 items-center justify-center rounded-2xl border text-sm transition ${
                active
                    ? 'border-cyan-300/60 bg-cyan-400/15 text-cyan-100'
                    : 'border-white/10 bg-white/4 text-white/85 hover:border-white/20 hover:bg-white/8 hover:text-cyan-100'
            } disabled:cursor-not-allowed disabled:opacity-40`}
        >
            <Icon className="h-4 w-4" />
        </button>
    )
}

export default function RichTextField({
    label,
    hint,
    error,
    required = false,
    disabled = false,
    value = '',
    onChange,
}) {
    const normalizedValue = typeof value === 'string' ? value : ''
    const extensions = useMemo(() => ([
        StarterKit.configure({
            heading: {
                levels: [2, 3, 4],
            },
        }),
        Underline,
        Highlight.configure({ multicolor: true }),
        Link.configure({
            openOnClick: false,
            autolink: true,
            defaultProtocol: 'https',
            HTMLAttributes: {
                rel: 'noopener noreferrer',
                target: '_blank',
            },
        }),
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        Placeholder.configure({
            placeholder: 'Escribe aqui el contenido con formato...',
        }),
    ]), [])

    const editor = useEditor({
        extensions,
        content: normalizedValue || '<p></p>',
        editable: !disabled,
        immediatelyRender: false,
        onUpdate: ({ editor: currentEditor }) => {
            onChange?.(currentEditor.isEmpty ? '' : currentEditor.getHTML())
        },
    })

    useEffect(() => {
        editor?.setEditable(!disabled)
    }, [editor, disabled])

    useEffect(() => {
        if (!editor) {
            return
        }

        const currentHtml = editor.isEmpty ? '' : editor.getHTML()

        if (currentHtml !== normalizedValue) {
            editor.commands.setContent(normalizedValue || '<p></p>', {
                emitUpdate: false,
            })
        }
    }, [editor, normalizedValue])

    const setLink = () => {
        if (!editor) {
            return
        }

        const previousUrl = editor.getAttributes('link').href ?? ''
        const url = window.prompt('Introduce la URL del enlace', previousUrl)

        if (url === null) {
            return
        }

        const trimmedUrl = url.trim()

        if (trimmedUrl === '') {
            editor.chain().focus().unsetLink().run()
            return
        }

        editor.chain().focus().extendMarkRange('link').setLink({ href: trimmedUrl }).run()
    }

    const toolbar = [
        { label: 'Deshacer', icon: FiRotateCcw, active: false, disabled: !editor?.can().chain().focus().undo().run(), onClick: () => editor?.chain().focus().undo().run() },
        { label: 'Rehacer', icon: FiRotateCw, active: false, disabled: !editor?.can().chain().focus().redo().run(), onClick: () => editor?.chain().focus().redo().run() },
        { label: 'Negrita', icon: FiBold, active: editor?.isActive('bold'), disabled, onClick: () => editor?.chain().focus().toggleBold().run() },
        { label: 'Cursiva', icon: FiItalic, active: editor?.isActive('italic'), disabled, onClick: () => editor?.chain().focus().toggleItalic().run() },
        { label: 'Subrayado', icon: FiUnderline, active: editor?.isActive('underline'), disabled, onClick: () => editor?.chain().focus().toggleUnderline().run() },
        { label: 'Resaltado', icon: FiEdit3, active: editor?.isActive('highlight'), disabled, onClick: () => editor?.chain().focus().toggleHighlight().run() },
        { label: 'Titulo H2', icon: FiType, active: editor?.isActive('heading', { level: 2 }), disabled, onClick: () => editor?.chain().focus().toggleHeading({ level: 2 }).run() },
        { label: 'Titulo H3', icon: FiType, active: editor?.isActive('heading', { level: 3 }), disabled, onClick: () => editor?.chain().focus().toggleHeading({ level: 3 }).run() },
        { label: 'Lista', icon: FiList, active: editor?.isActive('bulletList'), disabled, onClick: () => editor?.chain().focus().toggleBulletList().run() },
        { label: 'Lista numerada', icon: FiMenu, active: editor?.isActive('orderedList'), disabled, onClick: () => editor?.chain().focus().toggleOrderedList().run() },
        { label: 'Cita', icon: FiCornerDownLeft, active: editor?.isActive('blockquote'), disabled, onClick: () => editor?.chain().focus().toggleBlockquote().run() },
        { label: 'Codigo', icon: FiCode, active: editor?.isActive('codeBlock'), disabled, onClick: () => editor?.chain().focus().toggleCodeBlock().run() },
        { label: 'Linea horizontal', icon: FiMinus, active: false, disabled, onClick: () => editor?.chain().focus().setHorizontalRule().run() },
        { label: 'Enlace', icon: FiLink, active: editor?.isActive('link'), disabled, onClick: setLink },
        { label: 'Alinear izquierda', icon: FiAlignLeft, active: editor?.isActive({ textAlign: 'left' }), disabled, onClick: () => editor?.chain().focus().setTextAlign('left').run() },
        { label: 'Centrar', icon: FiAlignCenter, active: editor?.isActive({ textAlign: 'center' }), disabled, onClick: () => editor?.chain().focus().setTextAlign('center').run() },
        { label: 'Alinear derecha', icon: FiAlignRight, active: editor?.isActive({ textAlign: 'right' }), disabled, onClick: () => editor?.chain().focus().setTextAlign('right').run() },
        { label: 'Limpiar formato', icon: FiTrash2, active: false, disabled, onClick: () => editor?.chain().focus().clearNodes().unsetAllMarks().run() },
    ]

    return (
        <label className="block space-y-2 xl:col-span-2">
            <span className="text-sm font-medium text-white">
                {label}
                {required ? <span className="ml-1 text-rose-300">*</span> : null}
            </span>

            <div className={`overflow-hidden rounded-[28px] border bg-slate-900/80 ${
                error ? 'border-rose-400/60' : 'border-white/10'
            }`}>
                <div className="border-b border-white/10 bg-black/20 px-3 py-3">
                    <div className="flex flex-wrap gap-2">
                        {toolbar.map((item) => (
                            <ToolbarButton
                                key={item.label}
                                label={item.label}
                                icon={item.icon}
                                active={Boolean(item.active)}
                                disabled={Boolean(item.disabled) || !editor}
                                onClick={item.onClick}
                            />
                        ))}
                    </div>
                </div>

                <div className="backoffice-richtext min-h-72">
                    <EditorContent editor={editor} />
                </div>
            </div>

            {error ? <span className="block text-xs text-rose-200">{error}</span> : null}
            {hint ? <span className="block text-xs text-white/45">{hint}</span> : null}
        </label>
    )
}
