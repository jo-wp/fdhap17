import type { Action } from '../../types';
import type { SetSelection } from '../../private-types';
export declare function useHasAPossibleBulkAction<Item>(actions: Action<Item>[], item: Item): boolean;
export declare function useSomeItemHasAPossibleBulkAction<Item>(actions: Action<Item>[], data: Item[]): boolean;
interface BulkSelectionCheckboxProps<Item> {
    selection: string[];
    onChangeSelection: SetSelection;
    data: Item[];
    actions: Action<Item>[];
    getItemId: (item: Item) => string;
}
export declare function BulkSelectionCheckbox<Item>({ selection, onChangeSelection, data, actions, getItemId, }: BulkSelectionCheckboxProps<Item>): import("react").JSX.Element;
export declare function BulkActionsFooter(): import("react").JSX.Element;
export {};
//# sourceMappingURL=index.d.ts.map