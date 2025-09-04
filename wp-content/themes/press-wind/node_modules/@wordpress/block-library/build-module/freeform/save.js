/**
 * WordPress dependencies
 */
import { RawHTML } from '@wordpress/element';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save({
  attributes
}) {
  const {
    content
  } = attributes;
  return /*#__PURE__*/_jsx(RawHTML, {
    children: content
  });
}
//# sourceMappingURL=save.js.map