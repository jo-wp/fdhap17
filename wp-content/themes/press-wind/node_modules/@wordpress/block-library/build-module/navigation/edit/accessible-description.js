/**
 * WordPress dependencies
 */
import { VisuallyHidden } from '@wordpress/components';
import { jsx as _jsx } from "react/jsx-runtime";
export default function AccessibleDescription({
  id,
  children
}) {
  return /*#__PURE__*/_jsx(VisuallyHidden, {
    children: /*#__PURE__*/_jsx("div", {
      id: id,
      className: "wp-block-navigation__description",
      children: children
    })
  });
}
//# sourceMappingURL=accessible-description.js.map