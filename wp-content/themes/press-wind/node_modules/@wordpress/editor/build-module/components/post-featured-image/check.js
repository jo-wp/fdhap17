/**
 * Internal dependencies
 */
import PostTypeSupportCheck from '../post-type-support-check';
import ThemeSupportCheck from '../theme-support-check';

/**
 * Wrapper component that renders its children only if the post type supports a featured image
 * and the theme supports post thumbnails.
 *
 * @param {Object}  props          Props.
 * @param {Element} props.children Children to be rendered.
 *
 * @return {Component} The component to be rendered.
 */
import { jsx as _jsx } from "react/jsx-runtime";
function PostFeaturedImageCheck({
  children
}) {
  return /*#__PURE__*/_jsx(ThemeSupportCheck, {
    supportKeys: "post-thumbnails",
    children: /*#__PURE__*/_jsx(PostTypeSupportCheck, {
      supportKeys: "thumbnail",
      children: children
    })
  });
}
export default PostFeaturedImageCheck;
//# sourceMappingURL=check.js.map