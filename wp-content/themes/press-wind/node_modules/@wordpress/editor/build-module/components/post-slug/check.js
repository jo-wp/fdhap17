/**
 * Internal dependencies
 */
import PostTypeSupportCheck from '../post-type-support-check';

/**
 * Wrapper component that renders its children only if the post type supports the slug.
 *
 * @param {Object}  props          Props.
 * @param {Element} props.children Children to be rendered.
 *
 * @return {Component} The component to be rendered.
 */
import { jsx as _jsx } from "react/jsx-runtime";
export default function PostSlugCheck({
  children
}) {
  return /*#__PURE__*/_jsx(PostTypeSupportCheck, {
    supportKeys: "slug",
    children: children
  });
}
//# sourceMappingURL=check.js.map