/**
 * Returns an action object used to set which template is currently being used/edited.
 *
 * @param {string} id Template Id.
 *
 * @return {Object} Action object.
 */
export function setCurrentTemplateId(id: string): Object;
export * from "../dataviews/store/private-actions";
export function createTemplate(template: Object | null): ({ select, dispatch, registry }: {
    select: any;
    dispatch: any;
    registry: any;
}) => Promise<any>;
export function showBlockTypes(blockNames: string[]): ({ registry }: {
    registry: any;
}) => void;
export function hideBlockTypes(blockNames: string[]): ({ registry }: {
    registry: any;
}) => void;
export function saveDirtyEntities({ onSave, dirtyEntityRecords, entitiesToSkip, close }?: {
    onSave?: Function | undefined;
    dirtyEntityRecords?: object[] | undefined;
    entitiesToSkip?: object[] | undefined;
    close?: Function | undefined;
}): ({ registry }: {
    registry: any;
}) => void;
export function revertTemplate(template: Object, { allowUndo }?: {
    allowUndo?: boolean | undefined;
} | undefined): ({ registry }: {
    registry: any;
}) => Promise<void>;
export function removeTemplates(items: any[]): ({ registry }: {
    registry: any;
}) => Promise<void>;
//# sourceMappingURL=private-actions.d.ts.map