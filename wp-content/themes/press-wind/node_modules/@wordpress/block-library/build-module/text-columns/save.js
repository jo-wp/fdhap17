/**
 * WordPress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save({
  attributes
}) {
  const {
    width,
    content,
    columns
  } = attributes;
  return /*#__PURE__*/_jsx("div", {
    ...useBlockProps.save({
      className: `align${width} columns-${columns}`
    }),
    children: Array.from({
      length: columns
    }).map((_, index) => /*#__PURE__*/_jsx("div", {
      className: "wp-block-column",
      children: /*#__PURE__*/_jsx(RichText.Content, {
        tagName: "p",
        value: content?.[index]?.children
      })
    }, `column-${index}`))
  });
}
//# sourceMappingURL=save.js.map