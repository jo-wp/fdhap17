/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
export default function NextPageEdit() {
  return /*#__PURE__*/_jsx("div", {
    ...useBlockProps(),
    children: /*#__PURE__*/_jsx("span", {
      children: __('Page break')
    })
  });
}
//# sourceMappingURL=edit.js.map