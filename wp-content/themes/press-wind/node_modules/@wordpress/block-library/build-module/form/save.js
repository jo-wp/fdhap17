/**
 * WordPress dependencies
 */
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save({
  attributes
}) {
  const blockProps = useBlockProps.save();
  const {
    submissionMethod
  } = attributes;
  return /*#__PURE__*/_jsx("form", {
    ...blockProps,
    className: "wp-block-form",
    encType: submissionMethod === 'email' ? 'text/plain' : null,
    children: /*#__PURE__*/_jsx(InnerBlocks.Content, {})
  });
}
//# sourceMappingURL=save.js.map