/**
 * WordPress dependencies
 */
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { jsx as _jsx } from "react/jsx-runtime";
const TEMPLATE = [['core/paragraph', {
  placeholder: __('Add text or blocks that will display when a query returns no results.')
}]];
export default function QueryNoResultsEdit() {
  const blockProps = useBlockProps();
  const innerBlocksProps = useInnerBlocksProps(blockProps, {
    template: TEMPLATE
  });
  return /*#__PURE__*/_jsx("div", {
    ...innerBlocksProps
  });
}
//# sourceMappingURL=edit.js.map