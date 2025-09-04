import type { NormalizedFilter, View } from '../../types';
interface AddFilterProps {
    filters: NormalizedFilter[];
    view: View;
    onChangeView: (view: View) => void;
    setOpenedFilter: (filter: string | null) => void;
}
export declare function AddFilterDropdownMenu({ filters, view, onChangeView, setOpenedFilter, trigger, }: AddFilterProps & {
    trigger: React.ReactNode;
}): import("react").JSX.Element;
declare const _default: import("react").ForwardRefExoticComponent<AddFilterProps & import("react").RefAttributes<HTMLButtonElement>>;
export default _default;
//# sourceMappingURL=add-filter.d.ts.map