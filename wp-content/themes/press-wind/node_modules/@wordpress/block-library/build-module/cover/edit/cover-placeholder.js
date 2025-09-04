/**
 * WordPress dependencies
 */
import { BlockIcon, MediaPlaceholder } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { cover as icon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import { ALLOWED_MEDIA_TYPES } from '../shared';
import { jsx as _jsx } from "react/jsx-runtime";
export default function CoverPlaceholder({
  disableMediaButtons = false,
  children,
  onSelectMedia,
  onError,
  style,
  toggleUseFeaturedImage
}) {
  return /*#__PURE__*/_jsx(MediaPlaceholder, {
    icon: /*#__PURE__*/_jsx(BlockIcon, {
      icon: icon
    }),
    labels: {
      title: __('Cover'),
      instructions: __('Drag and drop onto this block, upload, or select existing media from your library.')
    },
    onSelect: onSelectMedia,
    accept: "image/*,video/*",
    allowedTypes: ALLOWED_MEDIA_TYPES,
    disableMediaButtons: disableMediaButtons,
    onToggleFeaturedImage: toggleUseFeaturedImage,
    onError: onError,
    style: style,
    children: children
  });
}
//# sourceMappingURL=cover-placeholder.js.map