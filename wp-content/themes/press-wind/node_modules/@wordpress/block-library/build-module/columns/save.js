/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { useInnerBlocksProps, useBlockProps } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save({
  attributes
}) {
  const {
    isStackedOnMobile,
    verticalAlignment
  } = attributes;
  const className = clsx({
    [`are-vertically-aligned-${verticalAlignment}`]: verticalAlignment,
    [`is-not-stacked-on-mobile`]: !isStackedOnMobile
  });
  const blockProps = useBlockProps.save({
    className
  });
  const innerBlocksProps = useInnerBlocksProps.save(blockProps);
  return /*#__PURE__*/_jsx("div", {
    ...innerBlocksProps
  });
}
//# sourceMappingURL=save.js.map