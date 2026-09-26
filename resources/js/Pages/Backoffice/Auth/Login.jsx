import { Head, useForm } from '@inertiajs/react'
import { FiArrowRight, FiEye, FiEyeOff } from 'react-icons/fi'
import { useState } from 'react'
import BackofficeLoginFooter from '../../../Components/Backoffice/BackofficeLoginFooter'

function FieldError({ message }) {
    if (!message) {
        return null
    }

    return <p className="mt-2 text-sm text-rose-200">{message}</p>
}

export default function Login({ title, description, footer }) {
    const [showPassword, setShowPassword] = useState(false)
    const form = useForm({
        email: '',
        password: '',
        remember: false,
    })

    const submit = (event) => {
        event.preventDefault()

        form.post('/backoffice/login', {
            onFinish: () => {
                form.reset('password')
            },
        })
    }

    return (
        <>
            <Head title={title}>
                <link rel="stylesheet" href="/assets/fonts/Inter/style.css" />
            </Head>

            <div className="min-h-screen overflow-hidden bg-black text-white">
                <div className="pointer-events-none fixed inset-0">
                    <div className="absolute inset-0 bg-[linear-gradient(-45deg,#ff1493,#000,#00ffff,#000,#8a2be2,#000,#ff00ff,#000,#1e90ff)] bg-[length:600%_600%] animate-[backoffice-login-gradient_15s_ease_infinite]" />
                    <div
                        className="absolute inset-0 opacity-[0.14]"
                        style={{ backgroundImage: "url('/assets/img/bg/bg-texture-2.png')", backgroundSize: 'cover', backgroundPosition: 'center' }}
                    />
                </div>

                <div className="relative z-10 flex min-h-screen flex-col">
                    <main className="flex flex-1 items-center justify-center px-5 py-8 pb-24 lg:pb-32">
                        <div className="w-full max-w-2xl">
                            <div className="mx-auto max-w-xl text-center">
                                <img
                                    src="/assets/img/logos/RC_Logo_white.svg"
                                    alt="RadioChi Backoffice"
                                    className="mx-auto mb-6 h-auto w-[min(15rem,65vw)] drop-shadow-[0_0_28px_rgba(255,255,255,0.2)]"
                                />
                                <h1 className="text-[clamp(2rem,4vw,2.75rem)] font-bold leading-none">{title}</h1>
                                <p className="mt-4 text-base text-white/70">{description}</p>
                            </div>

                            <div className="mt-8 rounded-[32px] border border-white/10 bg-black/35 p-6 shadow-[0_24px_80px_rgba(0,0,0,0.45)] backdrop-blur-sm sm:p-8">
                                <form className="space-y-5" onSubmit={submit}>
                                    <div>
                                        <label className="mb-2 block text-sm font-semibold text-white" htmlFor="email">
                                            Correo electrónico
                                        </label>
                                        <input
                                            id="email"
                                            type="email"
                                            value={form.data.email}
                                            onChange={(event) => form.setData('email', event.target.value)}
                                            className="h-14 w-full rounded-full border border-white/15 bg-white/[0.06] px-5 text-white outline-none transition placeholder:text-white/35 focus:border-fuchsia-400/70 focus:ring-4 focus:ring-fuchsia-500/20"
                                            placeholder="correo@radiochi.com"
                                            autoComplete="email"
                                        />
                                        <FieldError message={form.errors.email} />
                                    </div>

                                    <div>
                                        <label className="mb-2 block text-sm font-semibold text-white" htmlFor="password">
                                            Contraseña
                                        </label>
                                        <div className="flex h-14 items-center rounded-full border border-white/15 bg-white/[0.06] pr-2 transition focus-within:border-fuchsia-400/70 focus-within:ring-4 focus-within:ring-fuchsia-500/20">
                                            <input
                                                id="password"
                                                type={showPassword ? 'text' : 'password'}
                                                value={form.data.password}
                                                onChange={(event) => form.setData('password', event.target.value)}
                                                className="h-full min-w-0 flex-1 rounded-full bg-transparent px-5 text-white outline-none placeholder:text-white/35"
                                                placeholder="Tu contraseña"
                                                autoComplete="current-password"
                                            />
                                            <button
                                                type="button"
                                                onClick={() => setShowPassword((current) => !current)}
                                                className="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-black/20 text-white/75 transition hover:text-white"
                                                aria-label={showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'}
                                            >
                                                {showPassword ? <FiEyeOff className="h-4 w-4" /> : <FiEye className="h-4 w-4" />}
                                            </button>
                                        </div>
                                        <FieldError message={form.errors.password} />
                                    </div>

                                    <label className="flex items-center gap-3 text-sm text-white/80">
                                        <input
                                            type="checkbox"
                                            checked={form.data.remember}
                                            onChange={(event) => form.setData('remember', event.target.checked)}
                                            className="h-4 w-4 rounded border-white/20 bg-white/5 text-fuchsia-400 focus:ring-fuchsia-400/40"
                                        />
                                        <span>Recordarme</span>
                                    </label>

                                    <button
                                        type="submit"
                                        disabled={form.processing}
                                        className="inline-flex h-14 w-full items-center justify-center gap-3 rounded-full border border-cyan-300/75 bg-transparent text-base font-semibold text-cyan-100 transition-colors duration-200 hover:border-cyan-100 hover:bg-cyan-300 hover:text-slate-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-300/35 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950 disabled:cursor-not-allowed disabled:opacity-70"
                                    >
                                        <span>{form.processing ? 'Entrando...' : 'Entrar'}</span>
                                        <FiArrowRight className="h-5 w-5" />
                                    </button>
                                </form>
                            </div>

                            <BackofficeLoginFooter footer={footer} />
                        </div>
                    </main>
                </div>
            </div>
        </>
    )
}
