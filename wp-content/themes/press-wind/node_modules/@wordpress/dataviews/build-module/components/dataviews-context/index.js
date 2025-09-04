/**
 * WordPress dependencies
 */
import { createContext } from '@wordpress/element';

/**
 * Internal dependencies
 */

import { LAYOUT_TABLE } from '../../constants';
const DataViewsContext = createContext({
  view: {
    type: LAYOUT_TABLE
  },
  onChangeView: () => {},
  fields: [],
  data: [],
  paginationInfo: {
    totalItems: 0,
    totalPages: 0
  },
  selection: [],
  onChangeSelection: () => {},
  setOpenedFilter: () => {},
  openedFilter: null,
  getItemId: item => item.id,
  density: 0
});
export default DataViewsContext;
//# sourceMappingURL=index.js.map