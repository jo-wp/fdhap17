/**
 * WordPress dependencies
 */
import { useBlockProps, getSpacingPresetCssVar } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save({
  attributes
}) {
  const {
    height,
    width,
    style
  } = attributes;
  const {
    layout: {
      selfStretch
    } = {}
  } = style || {};
  // If selfStretch is set to 'fill' or 'fit', don't set default height.
  const finalHeight = selfStretch === 'fill' || selfStretch === 'fit' ? undefined : height;
  return /*#__PURE__*/_jsx("div", {
    ...useBlockProps.save({
      style: {
        height: getSpacingPresetCssVar(finalHeight),
        width: getSpacingPresetCssVar(width)
      },
      'aria-hidden': true
    })
  });
}
//# sourceMappingURL=save.js.map