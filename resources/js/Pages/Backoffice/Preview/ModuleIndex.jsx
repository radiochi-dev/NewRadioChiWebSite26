import CrudIndexScreen from '../../../Components/Backoffice/crud/CrudIndexScreen'
import MediaAssetIndexScreen from '../../../Components/Backoffice/media/MediaAssetIndexScreen'
import SeoMetaIndexScreen from '../../../Components/Backoffice/seo/SeoMetaIndexScreen'
import SettingsIndexScreen from '../../../Components/Backoffice/settings/SettingsIndexScreen'

export default function ModuleIndex(props) {
    if (props.module?.slug === 'media-assets') {
        return <MediaAssetIndexScreen {...props} />
    }

    if (props.module?.slug === 'seo-metas') {
        return <SeoMetaIndexScreen {...props} />
    }

    if (props.module?.slug === 'settings') {
        return <SettingsIndexScreen {...props} />
    }

    return <CrudIndexScreen {...props} />
}
