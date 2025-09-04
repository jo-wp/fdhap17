/**
 * Internal dependencies
 */
import type { Field } from '../../types';
import type { SetSelection } from '../../private-types';
interface DataViewsSelectionCheckboxProps<Item> {
    selection: string[];
    onChangeSelection: SetSelection;
    item: Item;
    getItemId: (item: Item) => string;
    primaryField?: Field<Item>;
    disabled: boolean;
}
export default function DataViewsSelectionCheckbox<Item>({ selection, onChangeSelection, item, getItemId, primaryField, disabled, }: DataViewsSelectionCheckboxProps<Item>): import("react").JSX.Element;
export {};
//# sourceMappingURL=index.d.ts.map