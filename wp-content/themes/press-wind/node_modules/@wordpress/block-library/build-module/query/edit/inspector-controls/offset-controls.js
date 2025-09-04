/**
 * WordPress dependencies
 */
import { __experimentalNumberControl as NumberControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { jsx as _jsx } from "react/jsx-runtime";
const MIN_OFFSET = 0;
const MAX_OFFSET = 100;
export const OffsetControl = ({
  offset = 0,
  onChange
}) => {
  return /*#__PURE__*/_jsx(NumberControl, {
    __next40pxDefaultSize: true,
    label: __('Offset'),
    value: offset,
    min: MIN_OFFSET,
    onChange: newOffset => {
      if (isNaN(newOffset) || newOffset < MIN_OFFSET || newOffset > MAX_OFFSET) {
        return;
      }
      onChange({
        offset: newOffset
      });
    }
  });
};
export default OffsetControl;
//# sourceMappingURL=offset-controls.js.map