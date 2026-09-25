import CrudIndexScreen from '../../../Components/Backoffice/crud/CrudIndexScreen'
import MediaAssetIndexScreen from '../../../Components/Backoffice/media/MediaAssetIndexScreen'

export default function ModuleIndex(props) {
    if (props.module?.slug === 'media-assets') {
        return <MediaAssetIndexScreen {...props} />
    }

    return <CrudIndexScreen {...props} />
}
