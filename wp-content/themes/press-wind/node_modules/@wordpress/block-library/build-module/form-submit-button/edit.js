/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
const TEMPLATE = [['core/buttons', {}, [['core/button', {
  text: __('Submit'),
  tagName: 'button',
  type: 'submit'
}]]]];
const Edit = () => {
  const blockProps = useBlockProps();
  const innerBlocksProps = useInnerBlocksProps(blockProps, {
    template: TEMPLATE,
    templateLock: 'all'
  });
  return /*#__PURE__*/_jsx("div", {
    className: "wp-block-form-submit-wrapper",
    ...innerBlocksProps
  });
};
export default Edit;
//# sourceMappingURL=edit.js.map