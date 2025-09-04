/**
 * External dependencies
 */
import type { PropsWithoutRef, RefAttributes } from 'react';
import type { NormalizedField, ViewTable as ViewTableType } from '../../types';
interface HeaderMenuProps<Item> {
    fieldId: string;
    view: ViewTableType;
    fields: NormalizedField<Item>[];
    onChangeView: (view: ViewTableType) => void;
    onHide: (field: NormalizedField<Item>) => void;
    setOpenedFilter: (fieldId: string) => void;
}
declare const _HeaderMenu: import("react").ForwardRefExoticComponent<HeaderMenuProps<unknown> & RefAttributes<HTMLButtonElement>>;
declare const ColumnHeaderMenu: <Item>(props: PropsWithoutRef<HeaderMenuProps<Item>> & RefAttributes<HTMLButtonElement>) => ReturnType<typeof _HeaderMenu>;
export default ColumnHeaderMenu;
//# sourceMappingURL=column-header-menu.d.ts.map