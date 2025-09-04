/**
 * WordPress dependencies
 */
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
const deprecated = [
// Version with wrapper `div` element.
{
  save() {
    return /*#__PURE__*/_jsx("div", {
      ...useBlockProps.save(),
      children: /*#__PURE__*/_jsx(InnerBlocks.Content, {})
    });
  }
}];
export default deprecated;
//# sourceMappingURL=deprecated.js.map