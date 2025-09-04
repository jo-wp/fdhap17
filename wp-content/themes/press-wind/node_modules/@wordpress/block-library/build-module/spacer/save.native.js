/**
 * WordPress dependencies
 */
import { useBlockProps, getSpacingPresetCssVar } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save({
  attributes: {
    height,
    width
  }
}) {
  return /*#__PURE__*/_jsx("div", {
    ...useBlockProps.save({
      style: {
        height: getSpacingPresetCssVar(height),
        width: getSpacingPresetCssVar(width)
      },
      'aria-hidden': true
    })
  });
}
//# sourceMappingURL=save.native.js.map