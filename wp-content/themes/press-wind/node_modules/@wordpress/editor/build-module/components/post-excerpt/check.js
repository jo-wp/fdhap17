/**
 * Internal dependencies
 */
import PostTypeSupportCheck from '../post-type-support-check';

/**
 * Component for checking if the post type supports the excerpt field.
 *
 * @param {Object}  props          Props.
 * @param {Element} props.children Children to be rendered.
 *
 * @return {Component} The component to be rendered.
 */
import { jsx as _jsx } from "react/jsx-runtime";
function PostExcerptCheck({
  children
}) {
  return /*#__PURE__*/_jsx(PostTypeSupportCheck, {
    supportKeys: "excerpt",
    children: children
  });
}
export default PostExcerptCheck;
//# sourceMappingURL=check.js.map