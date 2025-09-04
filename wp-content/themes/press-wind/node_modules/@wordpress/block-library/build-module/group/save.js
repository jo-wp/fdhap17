/**
 * WordPress dependencies
 */
import { useInnerBlocksProps, useBlockProps } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save({
  attributes: {
    tagName: Tag
  }
}) {
  return /*#__PURE__*/_jsx(Tag, {
    ...useInnerBlocksProps.save(useBlockProps.save())
  });
}
//# sourceMappingURL=save.js.map