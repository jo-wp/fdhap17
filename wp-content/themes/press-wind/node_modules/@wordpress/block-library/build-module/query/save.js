/**
 * WordPress dependencies
 */
import { useInnerBlocksProps, useBlockProps } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save({
  attributes: {
    tagName: Tag = 'div'
  }
}) {
  const blockProps = useBlockProps.save();
  const innerBlocksProps = useInnerBlocksProps.save(blockProps);
  return /*#__PURE__*/_jsx(Tag, {
    ...innerBlocksProps
  });
}
//# sourceMappingURL=save.js.map