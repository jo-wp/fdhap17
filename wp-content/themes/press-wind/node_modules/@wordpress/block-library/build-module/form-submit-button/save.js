/**
 * WordPress dependencies
 */
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save() {
  const blockProps = useBlockProps.save();
  return /*#__PURE__*/_jsx("div", {
    className: "wp-block-form-submit-wrapper",
    ...blockProps,
    children: /*#__PURE__*/_jsx(InnerBlocks.Content, {})
  });
}
//# sourceMappingURL=save.js.map