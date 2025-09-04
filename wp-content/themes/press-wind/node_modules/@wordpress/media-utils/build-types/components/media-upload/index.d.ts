export default MediaUpload;
declare class MediaUpload extends Component<any, any, any> {
    constructor(...args: any[]);
    openModal(): void;
    onOpen(): void;
    onSelect(): void;
    onUpdate(selections: any): void;
    onClose(): void;
    initializeListeners(): void;
    /**
     * Sets the Gallery frame and initializes listeners.
     *
     * @return {void}
     */
    buildAndSetGalleryFrame(): void;
    lastGalleryValue: any;
    GalleryDetailsMediaFrame: any;
    frame: any;
    /**
     * Initializes the Media Library requirements for the featured image flow.
     *
     * @return {void}
     */
    buildAndSetFeatureImageFrame(): void;
    componentWillUnmount(): void;
    updateCollection(): void;
    render(): any;
}
import { Component } from '@wordpress/element';
//# sourceMappingURL=index.d.ts.map