import { useEffect, useMemo, useRef, useState } from 'react'
import { FiChevronDown } from 'react-icons/fi'

function buildSyntheticEvent(value, name) {
    return {
        target: {
            value,
            name,
        },
        currentTarget: {
            value,
            name,
        },
    }
}

export default function Select({
    label,
    options = [],
    hint,
    error,
    required = false,
    className = '',
    value = '',
    onChange,
    disabled = false,
    name,
}) {
    const containerRef = useRef(null)
    const buttonRef = useRef(null)
    const listRef = useRef(null)
    const [open, setOpen] = useState(false)

    const normalizedValue = value ?? ''
    const selectedOption = useMemo(
        () => options.find((option) => String(option.value) === String(normalizedValue)) ?? null,
        [options, normalizedValue],
    )

    const activeIndex = useMemo(
        () => Math.max(
            0,
            options.findIndex((option) => String(option.value) === String(normalizedValue)),
        ),
        [options, normalizedValue],
    )

    useEffect(() => {
        if (!open) {
            return undefined
        }

        const handlePointerDown = (event) => {
            if (containerRef.current?.contains(event.target)) {
                return
            }

            setOpen(false)
        }

        const handleEscape = (event) => {
            if (event.key === 'Escape') {
                setOpen(false)
                buttonRef.current?.focus()
            }
        }

        window.addEventListener('mousedown', handlePointerDown)
        window.addEventListener('keydown', handleEscape)

        return () => {
            window.removeEventListener('mousedown', handlePointerDown)
            window.removeEventListener('keydown', handleEscape)
        }
    }, [open])

    useEffect(() => {
        if (!open) {
            return
        }

        const selectedNode = listRef.current?.querySelector('[data-selected="true"]')
        selectedNode?.scrollIntoView({ block: 'nearest' })
    }, [open])

    const commitValue = (nextValue) => {
        onChange?.(buildSyntheticEvent(nextValue, name))
        setOpen(false)
        buttonRef.current?.focus()
    }

    const handleButtonKeyDown = (event) => {
        if (disabled) {
            return
        }

        if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
            event.preventDefault()
            setOpen(true)
            return
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault()
            setOpen(true)
        }
    }

    const handleOptionKeyDown = (event, index) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault()
            commitValue(options[index].value)
            return
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault()
            const nextIndex = Math.min(index + 1, options.length - 1)
            listRef.current?.querySelector(`[data-option-index="${nextIndex}"]`)?.focus()
            return
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault()

            if (index === 0) {
                buttonRef.current?.focus()
                return
            }

            const previousIndex = Math.max(index - 1, 0)
            listRef.current?.querySelector(`[data-option-index="${previousIndex}"]`)?.focus()
            return
        }

        if (event.key === 'Escape') {
            event.preventDefault()
            setOpen(false)
            buttonRef.current?.focus()
        }
    }

    return (
        <div ref={containerRef} className={`relative block space-y-2 ${className}`}>
            <span className="text-sm font-medium text-white">
                {label}
                {required ? <span className="ml-1 text-rose-300">*</span> : null}
            </span>

            <button
                ref={buttonRef}
                type="button"
                name={name}
                disabled={disabled}
                aria-haspopup="listbox"
                aria-expanded={open}
                onClick={() => setOpen((current) => !current)}
                onKeyDown={handleButtonKeyDown}
                className={`backoffice-field-select-trigger relative w-full rounded-2xl border bg-slate-900/80 px-4 py-3 pr-14 text-left text-sm text-white outline-none transition disabled:cursor-not-allowed disabled:opacity-60 ${
                    error ? 'border-rose-400/60' : 'border-white/10 focus:border-cyan-300/60'
                } ${open ? 'border-cyan-300/60 shadow-[0_0_0_1px_rgba(103,232,249,0.24)]' : ''}`}
            >
                <span className={`block truncate ${selectedOption ? 'text-white' : 'text-white/30'}`}>
                    {selectedOption?.label ?? 'Selecciona una opcion'}
                </span>

                <span className={`pointer-events-none absolute inset-y-0 right-4 flex items-center transition ${open ? 'text-cyan-100' : 'text-white/80'}`}>
                    <FiChevronDown className={`h-4 w-4 transition ${open ? 'rotate-180' : ''}`} />
                </span>
            </button>

            {open ? (
                <div className="absolute left-0 right-0 top-[calc(100%+0.45rem)] z-40 overflow-hidden rounded-2xl border border-white/10 bg-slate-950/98 shadow-[0_24px_60px_rgba(0,0,0,0.45)] backdrop-blur-xl">
                    <div
                        ref={listRef}
                        role="listbox"
                        aria-label={label}
                        className="max-h-72 overflow-y-auto py-2"
                    >
                        {options.map((option, index) => {
                            const isSelected = String(option.value) === String(normalizedValue)

                            return (
                                <button
                                    key={`${option.value}-${index}`}
                                    type="button"
                                    role="option"
                                    aria-selected={isSelected}
                                    data-selected={isSelected}
                                    data-option-index={index}
                                    tabIndex={index === activeIndex ? 0 : -1}
                                    onClick={() => commitValue(option.value)}
                                    onKeyDown={(event) => handleOptionKeyDown(event, index)}
                                    className={`flex w-full items-center px-4 py-3 text-left text-sm transition ${
                                        isSelected
                                            ? 'bg-cyan-400/15 text-white'
                                            : 'text-white/78 hover:bg-white/6 hover:text-white'
                                    }`}
                                >
                                    <span className="truncate">{option.label}</span>
                                </button>
                            )
                        })}
                    </div>
                </div>
            ) : null}

            {error ? <span className="block text-xs text-rose-200">{error}</span> : null}
            {hint ? <span className="block text-xs text-white/45">{hint}</span> : null}
        </div>
    )
}
