import type { NormalizedFilter, NormalizedField, View } from '../../types';
export declare function useFilters(fields: NormalizedField<any>[], view: View): NormalizedFilter[];
export declare function FilterVisibilityToggle({ filters, view, onChangeView, setOpenedFilter, isShowingFilter, setIsShowingFilter, }: {
    filters: NormalizedFilter[];
    view: View;
    onChangeView: (view: View) => void;
    setOpenedFilter: (filter: string | null) => void;
    isShowingFilter: boolean;
    setIsShowingFilter: React.Dispatch<React.SetStateAction<boolean>>;
}): import("react").JSX.Element | null;
declare function Filters(): import("react").JSX.Element | null;
declare const _default: import("react").MemoExoticComponent<typeof Filters>;
export default _default;
//# sourceMappingURL=index.d.ts.map