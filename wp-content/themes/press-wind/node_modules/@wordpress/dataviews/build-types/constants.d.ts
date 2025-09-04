/**
 * Internal dependencies
 */
import type { Operator } from './types';
export declare const OPERATOR_IS = "is";
export declare const OPERATOR_IS_NOT = "isNot";
export declare const OPERATOR_IS_ANY = "isAny";
export declare const OPERATOR_IS_NONE = "isNone";
export declare const OPERATOR_IS_ALL = "isAll";
export declare const OPERATOR_IS_NOT_ALL = "isNotAll";
export declare const ALL_OPERATORS: string[];
export declare const OPERATORS: Record<Operator, {
    key: string;
    label: string;
}>;
export declare const SORTING_DIRECTIONS: readonly ["asc", "desc"];
export declare const sortArrows: {
    asc: string;
    desc: string;
};
export declare const sortValues: {
    readonly asc: "ascending";
    readonly desc: "descending";
};
export declare const sortLabels: {
    asc: string;
    desc: string;
};
export declare const sortIcons: {
    asc: import("react").JSX.Element;
    desc: import("react").JSX.Element;
};
export declare const LAYOUT_TABLE = "table";
export declare const LAYOUT_GRID = "grid";
export declare const LAYOUT_LIST = "list";
//# sourceMappingURL=constants.d.ts.map