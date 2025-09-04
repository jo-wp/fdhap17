/**
 * External dependencies
 */
import type { ReactNode } from 'react';
import type { Action, Field, View, SupportedLayouts } from '../../types';
type ItemWithId = {
    id: string;
};
type DataViewsProps<Item> = {
    view: View;
    onChangeView: (view: View) => void;
    fields: Field<Item>[];
    search?: boolean;
    searchLabel?: string;
    actions?: Action<Item>[];
    data: Item[];
    isLoading?: boolean;
    paginationInfo: {
        totalItems: number;
        totalPages: number;
    };
    defaultLayouts: SupportedLayouts;
    selection?: string[];
    onChangeSelection?: (items: string[]) => void;
    header?: ReactNode;
} & (Item extends ItemWithId ? {
    getItemId?: (item: Item) => string;
} : {
    getItemId: (item: Item) => string;
});
export default function DataViews<Item>({ view, onChangeView, fields, search, searchLabel, actions, data, getItemId, isLoading, paginationInfo, defaultLayouts, selection: selectionProperty, onChangeSelection, header, }: DataViewsProps<Item>): import("react").JSX.Element;
export {};
//# sourceMappingURL=index.d.ts.map