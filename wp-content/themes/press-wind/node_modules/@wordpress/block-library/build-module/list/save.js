/**
 * WordPress dependencies
 */
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save({
  attributes
}) {
  const {
    ordered,
    type,
    reversed,
    start
  } = attributes;
  const TagName = ordered ? 'ol' : 'ul';
  return /*#__PURE__*/_jsx(TagName, {
    ...useBlockProps.save({
      reversed,
      start,
      style: {
        listStyleType: ordered && type !== 'decimal' ? type : undefined
      }
    }),
    children: /*#__PURE__*/_jsx(InnerBlocks.Content, {})
  });
}
//# sourceMappingURL=save.js.map