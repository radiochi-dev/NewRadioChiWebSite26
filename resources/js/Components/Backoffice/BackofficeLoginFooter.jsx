import { AiFillInstagram } from 'react-icons/ai'
import { FaFacebookSquare, FaSpotify } from 'react-icons/fa'
import { ImSoundcloud2 } from 'react-icons/im'

function SocialIcon({ socialLink }) {
    const platform = (socialLink?.platform ?? socialLink?.iconKey ?? '').toLowerCase()

    const icon = (() => {
        switch (platform) {
        case 'facebook':
            return <FaFacebookSquare />
        case 'instagram':
            return <AiFillInstagram />
        case 'soundcloud':
            return <ImSoundcloud2 />
        case 'spotify':
            return <FaSpotify />
        default:
            return socialLink?.label ?? 'Link'
        }
    })()

    return (
        <a
            href={socialLink.url}
            target="_blank"
            rel="noreferrer"
            className="transition hover:text-gray-300"
            aria-label={socialLink.label}
            title={socialLink.label}
        >
            {icon}
        </a>
    )
}

export default function BackofficeLoginFooter({ footer }) {
    return (
        <>
            <footer className="radiochi-backoffice-footer mt-8 space-y-3 border-t border-white/15 px-2 pt-5 text-[11px] uppercase tracking-[0.12em] text-white/85 lg:hidden" aria-label="Footer del backoffice">
                <div className="flex flex-wrap items-center justify-center gap-3 text-center">
                    <span>{footer?.credits?.copyright ?? '© 2025 Copyright.'}</span>
                    {footer?.credits?.rights ? <span>{footer.credits.rights}</span> : null}
                </div>
                <div className="flex flex-wrap items-center justify-center gap-3 text-center">
                    {(footer?.legalLinks ?? []).map((legalLink) => (
                        <span key={legalLink.label}>{legalLink.label}</span>
                    ))}
                </div>
                <div className="flex flex-wrap items-center justify-center gap-3 text-center text-base">
                    {(footer?.socialLinks ?? []).map((socialLink) => (
                        <SocialIcon key={`${socialLink.label}-${socialLink.url}`} socialLink={socialLink} />
                    ))}
                </div>
            </footer>

            <footer className="legacy-fixed-footer pointer-events-auto hidden lg:grid" aria-label="Footer del backoffice">
                <div className="legacy-fixed-footer-left">
                    <span>{footer?.credits?.copyright ?? '© 2025 Copyright.'}</span>
                    {footer?.credits?.rights ? <span>{footer.credits.rights}</span> : null}
                </div>

                <div className="legacy-fixed-footer-center" aria-hidden="true">
                    {(footer?.legalLinks ?? []).map((legalLink) => (
                        <span key={legalLink.label} className="text-[14px] font-medium leading-none text-white">
                            {legalLink.label}
                        </span>
                    ))}
                </div>

                <div className="legacy-fixed-footer-right">
                    {(footer?.socialLinks ?? []).map((socialLink) => (
                        <SocialIcon key={`${socialLink.label}-${socialLink.url}`} socialLink={socialLink} />
                    ))}
                </div>
            </footer>
        </>
    )
}
