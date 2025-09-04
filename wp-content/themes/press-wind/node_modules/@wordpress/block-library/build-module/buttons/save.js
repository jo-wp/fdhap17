/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save({
  attributes,
  className
}) {
  const {
    fontSize,
    style
  } = attributes;
  const blockProps = useBlockProps.save({
    className: clsx(className, {
      'has-custom-font-size': fontSize || style?.typography?.fontSize
    })
  });
  const innerBlocksProps = useInnerBlocksProps.save(blockProps);
  return /*#__PURE__*/_jsx("div", {
    ...innerBlocksProps
  });
}
//# sourceMappingURL=save.js.map