/**
 * WordPress dependencies
 */
import { PanelRow } from '@wordpress/components';

/**
 * Internal dependencies
 */
import PostSlugForm from './';
import PostSlugCheck from './check';
import { jsx as _jsx } from "react/jsx-runtime";
export function PostSlug() {
  return /*#__PURE__*/_jsx(PostSlugCheck, {
    children: /*#__PURE__*/_jsx(PanelRow, {
      className: "editor-post-slug",
      children: /*#__PURE__*/_jsx(PostSlugForm, {})
    })
  });
}
export default PostSlug;
//# sourceMappingURL=panel.js.map