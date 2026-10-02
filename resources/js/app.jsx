import './bootstrap'
import './publicAssetManifestWarmup'
import '../css/app.css'
import { createInertiaApp } from '@inertiajs/react'
import { createRoot } from 'react-dom/client'

const pages = import.meta.glob('./Pages/**/*.jsx')

createInertiaApp({
    resolve: (name) => {
        const page = pages[`./Pages/${name}.jsx`]

        if (!page) {
            throw new Error(`Inertia page not found: ${name}`)
        }

        return page()
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />)
    },
})
