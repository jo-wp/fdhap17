/**
 * WordPress dependencies
 */
import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { jsx as _jsx } from "react/jsx-runtime";
const stickyOptions = [{
  label: __('Include'),
  value: ''
}, {
  label: __('Exclude'),
  value: 'exclude'
}, {
  label: __('Only'),
  value: 'only'
}];
export default function StickyControl({
  value,
  onChange
}) {
  return /*#__PURE__*/_jsx(SelectControl, {
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true,
    label: __('Sticky posts'),
    options: stickyOptions,
    value: value,
    onChange: onChange,
    help: __('Sticky posts always appear first, regardless of their publish date.')
  });
}
//# sourceMappingURL=sticky-control.js.map