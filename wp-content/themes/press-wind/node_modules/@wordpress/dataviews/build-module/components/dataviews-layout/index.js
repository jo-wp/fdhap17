/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import DataViewsContext from '../dataviews-context';
import { VIEW_LAYOUTS } from '../../dataviews-layouts';
import { jsx as _jsx } from "react/jsx-runtime";
export default function DataViewsLayout() {
  const {
    actions = [],
    data,
    fields,
    getItemId,
    isLoading,
    view,
    onChangeView,
    selection,
    onChangeSelection,
    setOpenedFilter,
    density
  } = useContext(DataViewsContext);
  const ViewComponent = VIEW_LAYOUTS.find(v => v.type === view.type)?.component;
  return /*#__PURE__*/_jsx(ViewComponent, {
    actions: actions,
    data: data,
    fields: fields,
    getItemId: getItemId,
    isLoading: isLoading,
    onChangeView: onChangeView,
    onChangeSelection: onChangeSelection,
    selection: selection,
    setOpenedFilter: setOpenedFilter,
    view: view,
    density: density
  });
}
//# sourceMappingURL=index.js.map