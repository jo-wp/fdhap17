/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { isRTL } from '@wordpress/i18n';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save({
  attributes
}) {
  const {
    align,
    content,
    dropCap,
    direction
  } = attributes;
  const className = clsx({
    'has-drop-cap': align === (isRTL() ? 'left' : 'right') || align === 'center' ? false : dropCap,
    [`has-text-align-${align}`]: align
  });
  return /*#__PURE__*/_jsx("p", {
    ...useBlockProps.save({
      className,
      dir: direction
    }),
    children: /*#__PURE__*/_jsx(RichText.Content, {
      value: content
    })
  });
}
//# sourceMappingURL=save.js.map