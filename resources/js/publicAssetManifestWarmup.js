const managedPublicAssets = import.meta.glob([
    '../images/legacy/**/*',
    '../images/logos/**/*',
    '../images/media/**/*',
], {
    eager: true,
    import: 'default',
    query: '?url',
})

void managedPublicAssets
