/**
 * WordPress dependencies
 */
import { useInnerBlocksProps, useBlockProps } from '@wordpress/block-editor';

/**
 * External dependencies
 */
import clsx from 'clsx';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save({
  attributes
}) {
  const {
    type
  } = attributes;
  return /*#__PURE__*/_jsx("div", {
    ...useInnerBlocksProps.save(useBlockProps.save({
      className: clsx('wp-block-form-submission-notification', {
        [`form-notification-type-${type}`]: type
      })
    }))
  });
}
//# sourceMappingURL=save.js.map