/**
 * WordPress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save({
  attributes
}) {
  const {
    content
  } = attributes;
  return /*#__PURE__*/_jsx("pre", {
    ...useBlockProps.save(),
    children: /*#__PURE__*/_jsx(RichText.Content, {
      value: content
    })
  });
}
//# sourceMappingURL=save.js.map