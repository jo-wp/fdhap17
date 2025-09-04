/**
 * Internal dependencies
 */
import type { View, Action, NormalizedField } from '../../types';
import type { SetSelection } from '../../private-types';
type DataViewsContextType<Item> = {
    view: View;
    onChangeView: (view: View) => void;
    fields: NormalizedField<Item>[];
    actions?: Action<Item>[];
    data: Item[];
    isLoading?: boolean;
    paginationInfo: {
        totalItems: number;
        totalPages: number;
    };
    selection: string[];
    onChangeSelection: SetSelection;
    openedFilter: string | null;
    setOpenedFilter: (openedFilter: string | null) => void;
    getItemId: (item: Item) => string;
    density: number;
};
declare const DataViewsContext: import("react").Context<DataViewsContextType<any>>;
export default DataViewsContext;
//# sourceMappingURL=index.d.ts.map