/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

/**
 * Internal dependencies
 */
import { store as editorStore } from '../../store';
import PostFeaturedImage from './index';
import PostFeaturedImageCheck from './check';
import { jsx as _jsx } from "react/jsx-runtime";
const PANEL_NAME = 'featured-image';

/**
 * Renders the panel for the post featured image.
 *
 * @param {Object}  props               Props.
 * @param {boolean} props.withPanelBody Whether to include the panel body. Default true.
 *
 * @return {Component|null} The component to be rendered.
 * Return Null if the editor panel is disabled for featured image.
 */
export default function PostFeaturedImagePanel({
  withPanelBody = true
}) {
  var _postType$labels$feat;
  const {
    postType,
    isEnabled,
    isOpened
  } = useSelect(select => {
    const {
      getEditedPostAttribute,
      isEditorPanelEnabled,
      isEditorPanelOpened
    } = select(editorStore);
    const {
      getPostType
    } = select(coreStore);
    return {
      postType: getPostType(getEditedPostAttribute('type')),
      isEnabled: isEditorPanelEnabled(PANEL_NAME),
      isOpened: isEditorPanelOpened(PANEL_NAME)
    };
  }, []);
  const {
    toggleEditorPanelOpened
  } = useDispatch(editorStore);
  if (!isEnabled) {
    return null;
  }
  if (!withPanelBody) {
    return /*#__PURE__*/_jsx(PostFeaturedImageCheck, {
      children: /*#__PURE__*/_jsx(PostFeaturedImage, {})
    });
  }
  return /*#__PURE__*/_jsx(PostFeaturedImageCheck, {
    children: /*#__PURE__*/_jsx(PanelBody, {
      title: (_postType$labels$feat = postType?.labels?.featured_image) !== null && _postType$labels$feat !== void 0 ? _postType$labels$feat : __('Featured image'),
      opened: isOpened,
      onToggle: () => toggleEditorPanelOpened(PANEL_NAME),
      children: /*#__PURE__*/_jsx(PostFeaturedImage, {})
    })
  });
}
//# sourceMappingURL=panel.js.map