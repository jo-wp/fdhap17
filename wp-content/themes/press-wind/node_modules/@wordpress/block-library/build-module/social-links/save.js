/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { useInnerBlocksProps, useBlockProps } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save(props) {
  const {
    attributes: {
      iconBackgroundColorValue,
      iconColorValue,
      showLabels,
      size
    }
  } = props;
  const className = clsx(size, {
    'has-visible-labels': showLabels,
    'has-icon-color': iconColorValue,
    'has-icon-background-color': iconBackgroundColorValue
  });
  const blockProps = useBlockProps.save({
    className
  });
  const innerBlocksProps = useInnerBlocksProps.save(blockProps);
  return /*#__PURE__*/_jsx("ul", {
    ...innerBlocksProps
  });
}
//# sourceMappingURL=save.js.map