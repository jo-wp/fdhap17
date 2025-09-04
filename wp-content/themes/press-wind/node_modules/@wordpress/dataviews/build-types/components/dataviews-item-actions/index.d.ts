/**
 * External dependencies
 */
import type { MouseEventHandler, ReactElement } from 'react';
import type { Action, ActionModal as ActionModalType } from '../../types';
export interface ActionTriggerProps<Item> {
    action: Action<Item>;
    onClick: MouseEventHandler;
    isBusy?: boolean;
    items: Item[];
}
interface ActionModalProps<Item> {
    action: ActionModalType<Item>;
    items: Item[];
    closeModal?: () => void;
}
interface ActionWithModalProps<Item> extends ActionModalProps<Item> {
    ActionTrigger: (props: ActionTriggerProps<Item>) => ReactElement;
    isBusy?: boolean;
}
interface ActionsDropdownMenuGroupProps<Item> {
    actions: Action<Item>[];
    item: Item;
}
interface ItemActionsProps<Item> {
    item: Item;
    actions: Action<Item>[];
    isCompact?: boolean;
}
export declare function ActionModal<Item>({ action, items, closeModal, }: ActionModalProps<Item>): import("react").JSX.Element;
export declare function ActionWithModal<Item>({ action, items, ActionTrigger, isBusy, }: ActionWithModalProps<Item>): import("react").JSX.Element;
export declare function ActionsDropdownMenuGroup<Item>({ actions, item, }: ActionsDropdownMenuGroupProps<Item>): import("react").JSX.Element;
export default function ItemActions<Item>({ item, actions, isCompact, }: ItemActionsProps<Item>): import("react").JSX.Element;
export {};
//# sourceMappingURL=index.d.ts.map