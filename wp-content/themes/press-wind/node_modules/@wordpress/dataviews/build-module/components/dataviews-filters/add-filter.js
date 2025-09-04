/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */
import { privateApis as componentsPrivateApis, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { forwardRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { unlock } from '../../lock-unlock';
import { jsx as _jsx } from "react/jsx-runtime";
const {
  DropdownMenuV2
} = unlock(componentsPrivateApis);
export function AddFilterDropdownMenu({
  filters,
  view,
  onChangeView,
  setOpenedFilter,
  trigger
}) {
  const inactiveFilters = filters.filter(filter => !filter.isVisible);
  return /*#__PURE__*/_jsx(DropdownMenuV2, {
    trigger: trigger,
    children: inactiveFilters.map(filter => {
      return /*#__PURE__*/_jsx(DropdownMenuV2.Item, {
        onClick: () => {
          setOpenedFilter(filter.field);
          onChangeView({
            ...view,
            page: 1,
            filters: [...(view.filters || []), {
              field: filter.field,
              value: undefined,
              operator: filter.operators[0]
            }]
          });
        },
        children: /*#__PURE__*/_jsx(DropdownMenuV2.ItemLabel, {
          children: filter.name
        })
      }, filter.field);
    })
  });
}
function AddFilter({
  filters,
  view,
  onChangeView,
  setOpenedFilter
}, ref) {
  if (!filters.length || filters.every(({
    isPrimary
  }) => isPrimary)) {
    return null;
  }
  const inactiveFilters = filters.filter(filter => !filter.isVisible);
  return /*#__PURE__*/_jsx(AddFilterDropdownMenu, {
    trigger: /*#__PURE__*/_jsx(Button, {
      accessibleWhenDisabled: true,
      size: "compact",
      className: "dataviews-filters-button",
      variant: "tertiary",
      disabled: !inactiveFilters.length,
      ref: ref,
      children: __('Add filter')
    }),
    filters,
    view,
    onChangeView,
    setOpenedFilter
  });
}
export default forwardRef(AddFilter);
//# sourceMappingURL=add-filter.js.map