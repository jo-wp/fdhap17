/**
 * WordPress dependencies
 */
import { PanelBody } from '@wordpress/components';

/**
 * Internal dependencies
 */
import PostLastRevision from './';
import PostLastRevisionCheck from './check';

/**
 * Renders the panel for displaying the last revision of a post.
 *
 * @return {Component} The component to be rendered.
 */
import { jsx as _jsx } from "react/jsx-runtime";
function PostLastRevisionPanel() {
  return /*#__PURE__*/_jsx(PostLastRevisionCheck, {
    children: /*#__PURE__*/_jsx(PanelBody, {
      className: "editor-post-last-revision__panel",
      children: /*#__PURE__*/_jsx(PostLastRevision, {})
    })
  });
}
export default PostLastRevisionPanel;
//# sourceMappingURL=panel.js.map