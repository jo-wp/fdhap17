/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import PostTypeSupportCheck from '../post-type-support-check';
import { store as editorStore } from '../../store';
import { jsx as _jsx } from "react/jsx-runtime";
function PostFormatCheck({
  children
}) {
  const disablePostFormats = useSelect(select => select(editorStore).getEditorSettings().disablePostFormats, []);
  if (disablePostFormats) {
    return null;
  }
  return /*#__PURE__*/_jsx(PostTypeSupportCheck, {
    supportKeys: "post-formats",
    children: children
  });
}

/**
 * Component check if there are any post formats.
 *
 * @param {Object}  props          The component props.
 * @param {Element} props.children The child elements to render.
 *
 * @return {Component|null} The rendered component or null if post formats are disabled.
 */
export default PostFormatCheck;
//# sourceMappingURL=check.js.map