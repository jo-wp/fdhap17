/**
 * @typedef {import('@wordpress/dataviews').Action} Action
 */
/**
 * Registers a new DataViews action.
 *
 * This is an experimental API and is subject to change.
 * it's only available in the Gutenberg plugin for now.
 *
 * @param {string} kind   Entity kind.
 * @param {string} name   Entity name.
 * @param {Action} config Action configuration.
 */
export function registerEntityAction(kind: string, name: string, config: Action): void;
/**
 * Unregisters a DataViews action.
 *
 * This is an experimental API and is subject to change.
 * it's only available in the Gutenberg plugin for now.
 *
 * @param {string} kind     Entity kind.
 * @param {string} name     Entity name.
 * @param {string} actionId Action ID.
 */
export function unregisterEntityAction(kind: string, name: string, actionId: string): void;
export type Action = any;
//# sourceMappingURL=api.d.ts.map