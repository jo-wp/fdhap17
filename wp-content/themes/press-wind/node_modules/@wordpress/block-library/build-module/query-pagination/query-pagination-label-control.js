/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { ToggleControl } from '@wordpress/components';
import { jsx as _jsx } from "react/jsx-runtime";
export function QueryPaginationLabelControl({
  value,
  onChange
}) {
  return /*#__PURE__*/_jsx(ToggleControl, {
    __nextHasNoMarginBottom: true,
    label: __('Show label text'),
    help: __('Toggle off to hide the label text, e.g. "Next Page".'),
    onChange: onChange,
    checked: value === true
  });
}
//# sourceMappingURL=query-pagination-label-control.js.map