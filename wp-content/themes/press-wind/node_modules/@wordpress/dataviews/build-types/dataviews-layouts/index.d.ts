/**
 * Internal dependencies
 */
import ViewTable from './table';
import ViewGrid from './grid';
import ViewList from './list';
import type { View, Field } from '../types';
export declare const VIEW_LAYOUTS: ({
    type: string;
    label: string;
    component: typeof ViewTable;
    icon: import("react").JSX.Element;
} | {
    type: string;
    label: string;
    component: typeof ViewGrid;
    icon: import("react").JSX.Element;
} | {
    type: string;
    label: string;
    component: typeof ViewList;
    icon: import("react").JSX.Element;
})[];
export declare function getNotHidableFieldIds(view: View): string[];
export declare function getVisibleFieldIds(view: View, fields: Field<any>[]): string[];
export declare function getHiddenFieldIds(view: View, fields: Field<any>[]): string[];
//# sourceMappingURL=index.d.ts.map