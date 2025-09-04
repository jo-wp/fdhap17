/**
 * WordPress dependencies
 */
import { RawHTML } from '@wordpress/element';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save({
  attributes
}) {
  // Preserve the missing block's content.
  return /*#__PURE__*/_jsx(RawHTML, {
    children: attributes.originalContent
  });
}
//# sourceMappingURL=save.js.map