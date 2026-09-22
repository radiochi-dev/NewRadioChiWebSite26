const GA4_SCRIPT_ID = 'radiochi-ga4-script'

const resolveGa4Config = (analytics) => analytics?.ga4 ?? null

const canLoadGa4 = (analytics) => {
    const ga4 = resolveGa4Config(analytics)

    return Boolean(ga4?.loadScript && ga4?.measurementId)
}

export const initializePublicAnalytics = (analytics) => {
    if (!canLoadGa4(analytics) || typeof window === 'undefined' || typeof document === 'undefined') {
        return false
    }

    const { measurementId } = resolveGa4Config(analytics)

    window.dataLayer = window.dataLayer || []

    if (window.__radiochiGa4MeasurementId !== measurementId) {
        window.gtag = function gtag() {
            window.dataLayer.push(arguments)
        }

        window.gtag('js', new Date())
        window.gtag('config', measurementId, { send_page_view: false })
        window.__radiochiGa4MeasurementId = measurementId
    }

    if (!document.getElementById(GA4_SCRIPT_ID)) {
        const script = document.createElement('script')
        script.id = GA4_SCRIPT_ID
        script.async = true
        script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(measurementId)}`
        document.head.appendChild(script)
    }

    return true
}

export const trackPublicPageView = (analytics, { title, path, locale }) => {
    if (!initializePublicAnalytics(analytics) || typeof window.gtag !== 'function') {
        return
    }

    window.gtag('event', 'page_view', {
        page_title: title,
        page_path: path ?? window.location.pathname,
        page_location: window.location.href,
        language: locale,
    })
}

export const trackPublicEvent = (analytics, eventName, params = {}) => {
    if (!initializePublicAnalytics(analytics) || typeof window.gtag !== 'function') {
        return
    }

    window.gtag('event', eventName, params)
}
