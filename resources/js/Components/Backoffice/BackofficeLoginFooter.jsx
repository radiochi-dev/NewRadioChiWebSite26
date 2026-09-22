export default function BackofficeLoginFooter({ footer }) {
    return (
        <footer
            className="radiochi-backoffice-footer mt-8 grid w-full gap-3 border-t border-white/15 px-2 pt-5 text-[11px] uppercase tracking-[0.12em] text-white/85 lg:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)]"
            aria-label="Footer del backoffice"
        >
            <div className="flex flex-wrap items-center justify-center gap-3 text-center lg:justify-start lg:text-left">
                <span>{footer?.credits?.copyright ?? '© 2025 Copyright.'}</span>
                {footer?.credits?.rights ? <span>{footer.credits.rights}</span> : null}
            </div>

            <div className="flex flex-wrap items-center justify-center gap-3 text-center" aria-hidden="true">
                {(footer?.legalLinks ?? []).map((legalLink) => (
                    <span key={legalLink.label}>{legalLink.label}</span>
                ))}
            </div>

            <div className="flex flex-wrap items-center justify-center gap-3 text-center lg:justify-end lg:text-right">
                {(footer?.socialLinks ?? []).map((socialLink) => (
                    <a
                        key={`${socialLink.label}-${socialLink.url}`}
                        href={socialLink.url}
                        target="_blank"
                        rel="noreferrer"
                        className="transition hover:text-cyan-300"
                    >
                        {socialLink.label}
                    </a>
                ))}
            </div>
        </footer>
    )
}
