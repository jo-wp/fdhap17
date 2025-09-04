import type { Action } from '@wordpress/dataviews';
export declare function registerEntityAction<Item>(kind: string, name: string, config: Action<Item>): {
    type: "REGISTER_ENTITY_ACTION";
    kind: string;
    name: string;
    config: Action<Item>;
};
export declare function unregisterEntityAction(kind: string, name: string, actionId: string): {
    type: "UNREGISTER_ENTITY_ACTION";
    kind: string;
    name: string;
    actionId: string;
};
export declare function setIsReady(kind: string, name: string): {
    type: "SET_IS_READY";
    kind: string;
    name: string;
};
export declare const registerPostTypeActions: (postType: string) => ({ registry }: {
    registry: any;
}) => Promise<void>;
//# sourceMappingURL=private-actions.d.ts.map