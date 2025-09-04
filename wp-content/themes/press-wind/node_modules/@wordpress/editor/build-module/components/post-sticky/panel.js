/**
 * Internal dependencies
 */
import PostPanelRow from '../post-panel-row';
import PostStickyForm from './';
import PostStickyCheck from './check';
import { jsx as _jsx } from "react/jsx-runtime";
export function PostStickyPanel() {
  return /*#__PURE__*/_jsx(PostStickyCheck, {
    children: /*#__PURE__*/_jsx(PostPanelRow, {
      children: /*#__PURE__*/_jsx(PostStickyForm, {})
    })
  });
}
export default PostStickyPanel;
//# sourceMappingURL=panel.js.map