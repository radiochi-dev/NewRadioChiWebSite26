import { useMemo } from 'react'
import DOMPurify from 'dompurify'

const PUBLIC_HTML_SANITIZER_CONFIG = {
    USE_PROFILES: { html: true },
    FORBID_TAGS: ['style', 'script', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'base', 'meta', 'link'],
    FORBID_ATTR: ['style'],
    ALLOW_DATA_ATTR: false,
}

export default function SafeHtml({ as: Tag = 'div', html, ...props }) {
    const sanitizedHtml = useMemo(
        () => DOMPurify.sanitize(html ?? '', PUBLIC_HTML_SANITIZER_CONFIG),
        [html],
    )

    return <Tag {...props} dangerouslySetInnerHTML={{ __html: sanitizedHtml }} />
}
