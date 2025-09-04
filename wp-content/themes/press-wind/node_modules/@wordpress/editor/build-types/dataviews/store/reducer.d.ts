import type { Action } from '@wordpress/dataviews';
type ReduxAction = ReturnType<typeof import('./private-actions').registerEntityAction> | ReturnType<typeof import('./private-actions').unregisterEntityAction> | ReturnType<typeof import('./private-actions').setIsReady>;
export type ActionState = Record<string, Record<string, Action<any>[]>>;
export type ReadyState = Record<string, Record<string, boolean>>;
export type State = {
    actions: ActionState;
    isReady: ReadyState;
};
declare const _default: import("redux").Reducer<import("redux").CombinedState<{
    actions: {
        [x: string]: Record<string, Action<any>[]> | {
            [x: string]: Action<any>[] | (Action<unknown> | Action<any>)[];
        };
    };
    isReady: ReadyState;
}>, ReduxAction>;
export default _default;
//# sourceMappingURL=reducer.d.ts.map