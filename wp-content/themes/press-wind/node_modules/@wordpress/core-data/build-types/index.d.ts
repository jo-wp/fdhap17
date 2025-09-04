/**
 * Store definition for the code data namespace.
 *
 * @see https://github.com/WordPress/gutenberg/blob/HEAD/packages/data/README.md#createReduxStore
 */
export const store: import("@wordpress/data/build-types/types").StoreDescriptor<import("@wordpress/data/build-types/types").ReduxStoreConfig<any, {
    __unstableAcquireStoreLock: (store: any, path: any, { exclusive }: {
        exclusive: any;
    }) => () => Promise<any>;
    __unstableReleaseStoreLock: (lock: any) => () => void;
    receiveUserQuery(queryID: string, users: any[] | any): any;
    receiveCurrentUser(currentUser: any): any;
    addEntities(entities: any[]): any;
    receiveEntityRecords(kind: string, name: string, records: any[] | any, query: any | null, invalidateCache: (boolean | null) | undefined, edits: any | null, meta: any | null): any;
    receiveCurrentTheme(currentTheme: any): any;
    __experimentalReceiveCurrentGlobalStylesId(currentGlobalStylesId: string): any;
    __experimentalReceiveThemeBaseGlobalStyles(stylesheet: string, globalStyles: any): any;
    __experimentalReceiveThemeGlobalStyleVariations(stylesheet: string, variations: any[]): any;
    receiveThemeSupports(): any;
    receiveThemeGlobalStyleRevisions(currentId: number, revisions: any[]): any;
    receiveEmbedPreview(url: string, preview: any): any;
    receiveUploadPermissions(hasUploadPermissions: boolean): any;
    receiveUserPermission(key: string, isAllowed: boolean): any;
    receiveUserPermissions(permissions: {
        [x: string]: boolean;
    }): any;
    receiveAutosaves(postId: number, autosaves: any[] | any): any;
    receiveNavigationFallbackId(fallbackId: integer): any;
    receiveDefaultTemplateId(query: any, templateId: string): any;
    deleteEntityRecord: (kind: string, name: string, recordId: number | string, query: any | null, { __unstableFetch, throwOnError }?: {
        __unstableFetch?: Function | undefined;
        throwOnError?: boolean | undefined;
    } | undefined) => ({ dispatch }: {
        dispatch: any;
    }) => Promise<boolean | undefined>;
    editEntityRecord: (kind: string, name: string, recordId: number | string, edits: any, options?: {
        undoIgnore?: boolean | undefined;
    }) => any;
    undo: () => ({ select, dispatch }: {
        select: any;
        dispatch: any;
    }) => void;
    redo: () => ({ select, dispatch }: {
        select: any;
        dispatch: any;
    }) => void;
    __unstableCreateUndoLevel: () => any;
    saveEntityRecord: (kind: string, name: string, record: any, { isAutosave, __unstableFetch, throwOnError, }?: {
        isAutosave?: boolean | undefined;
        __unstableFetch?: Function | undefined;
        throwOnError?: boolean | undefined;
    }) => ({ select, resolveSelect, dispatch }: {
        select: any;
        resolveSelect: any;
        dispatch: any;
    }) => Promise<any>;
    __experimentalBatch: (requests: any[]) => (thunkArgs: any) => Promise<any>;
    saveEditedEntityRecord: (kind: string, name: string, recordId: any, options?: any | undefined) => ({ select, dispatch }: {
        select: any;
        dispatch: any;
    }) => Promise<any>;
    __experimentalSaveSpecifiedEntityEdits: (kind: string, name: string, recordId: number | string, itemsToSave: any[], options: any) => ({ select, dispatch }: {
        select: any;
        dispatch: any;
    }) => Promise<any>;
    receiveRevisions: (kind: string, name: string, recordKey: number | string, records: any[] | any, query: any | null, invalidateCache: (boolean | null) | undefined, meta: any | null) => ({ dispatch }: {
        dispatch: any;
    }) => Promise<void>;
}, {
    getAuthors(state: selectors.State, query?: {
        [x: string]: any;
    }): import("./entity-types").User[];
    getCurrentUser(state: selectors.State): import("./entity-types").User<"edit">;
    getEntitiesByKind(state: selectors.State, kind: string): Array<any>;
    getEntity(state: selectors.State, kind: string, name: string): any;
    getEntityConfig(state: selectors.State, kind: string, name: string): any;
    __experimentalGetEntityRecordNoResolver<EntityRecord extends import("./entity-types").EntityRecord<any>>(state: selectors.State, kind: string, name: string, key: string | number): EntityRecord | undefined;
    hasEntityRecords(state: selectors.State, kind: string, name: string, query?: {
        [x: string]: any;
    }): boolean;
    getEntityRecordEdits(state: selectors.State, kind: string, name: string, recordId: string | number): any;
    hasEditsForEntityRecord(state: selectors.State, kind: string, name: string, recordId: string | number): boolean;
    isAutosavingEntityRecord(state: selectors.State, kind: string, name: string, recordId: string | number): boolean;
    isSavingEntityRecord(state: selectors.State, kind: string, name: string, recordId: string | number): boolean;
    isDeletingEntityRecord(state: selectors.State, kind: string, name: string, recordId: string | number): boolean;
    getLastEntitySaveError(state: selectors.State, kind: string, name: string, recordId: string | number): any;
    getLastEntityDeleteError(state: selectors.State, kind: string, name: string, recordId: string | number): any;
    getUndoEdit(state: selectors.State): any;
    getRedoEdit(state: selectors.State): any;
    hasUndo(state: selectors.State): boolean;
    hasRedo(state: selectors.State): boolean;
    getCurrentTheme(state: selectors.State): any;
    __experimentalGetCurrentGlobalStylesId(state: selectors.State): string;
    getThemeSupports(state: selectors.State): any;
    getEmbedPreview(state: selectors.State, url: string): any;
    isPreviewEmbedFallback(state: selectors.State, url: string): boolean;
    canUser(state: selectors.State, action: string, resource: string | {
        kind: string;
        name: string;
        id?: string | number;
    }, id?: string | number): boolean | undefined;
    canUserEditEntityRecord(state: selectors.State, kind: string, name: string, recordId: string | number): boolean | undefined;
    getAutosaves(state: selectors.State, postType: string, postId: string | number): Array<any> | undefined;
    getAutosave<EntityRecord extends import("./entity-types").EntityRecord<any>>(state: selectors.State, postType: string, postId: string | number, authorId: string | number): EntityRecord | undefined;
    getReferenceByDistinctEdits(state: any): any;
    __experimentalGetTemplateForLink(state: selectors.State, link: string): (import("./entity-types").Updatable<import("./entity-types").WpTemplate> | undefined) | null | false;
    __experimentalGetCurrentThemeBaseGlobalStyles(state: selectors.State): any;
    __experimentalGetCurrentThemeGlobalStylesVariations(state: selectors.State): string | null;
    getBlockPatterns(state: selectors.State): Array<any>;
    getBlockPatternCategories(state: selectors.State): Array<any>;
    getUserPatternCategories(state: selectors.State): Array<selectors.UserPatternCategory>;
    getCurrentThemeGlobalStylesRevisions(state: selectors.State): Array<object> | null;
    getDefaultTemplateId(state: selectors.State, query: {
        slug?: string;
        is_custom?: boolean;
        ignore_empty?: boolean;
    }): string;
    isRequestingEmbedPreview: Function;
    getUserQueryResults: ((state: selectors.State, queryID: string) => import("./entity-types").User<"edit">[]) & import("rememo").EnhancedSelector;
    getEntitiesConfig: ((state: selectors.State, kind: string) => Array<any>) & import("rememo").EnhancedSelector;
    getEntityRecord: selectors.GetEntityRecord;
    getRawEntityRecord: (<EntityRecord extends import("./entity-types").EntityRecord<any>>(state: selectors.State, kind: string, name: string, key: string | number) => EntityRecord | undefined) & import("rememo").EnhancedSelector;
    getEntityRecords: selectors.GetEntityRecords;
    getEntityRecordsTotalItems: (state: selectors.State, kind: string, name: string, query: {
        [x: string]: any;
    }) => number | null;
    getEntityRecordsTotalPages: (state: selectors.State, kind: string, name: string, query: {
        [x: string]: any;
    }) => number | null;
    __experimentalGetDirtyEntityRecords: ((state: selectors.State) => Array<{
        title: string;
        key: string | number;
        name: string;
        kind: string;
    }>) & import("rememo").EnhancedSelector;
    __experimentalGetEntitiesBeingSaved: ((state: selectors.State) => Array<{
        title: string;
        key: string | number;
        name: string;
        kind: string;
    }>) & import("rememo").EnhancedSelector;
    getEntityRecordNonTransientEdits: ((state: selectors.State, kind: string, name: string, recordId: string | number) => any) & import("rememo").EnhancedSelector;
    getEditedEntityRecord: (<EntityRecord extends import("./entity-types").EntityRecord<any>>(state: selectors.State, kind: string, name: string, recordId: string | number) => import("./entity-types").Updatable<EntityRecord> | false) & import("rememo").EnhancedSelector;
    hasFetchedAutosaves: Function;
    getRevisions: (state: selectors.State, kind: string, name: string, recordKey: string | number, query?: {
        [x: string]: any;
    }) => (Record<import("./entity-types").Context, Record<number, import("./entity-types").PostRevision>> | Record<import("./entity-types").Context, Record<number, import("./entity-types").GlobalStylesRevision>>)[] | null;
    getRevision: ((state: selectors.State, kind: string, name: string, recordKey: string | number, revisionKey: string | number, query?: {
        [x: string]: any;
    }) => (Record<import("./entity-types").Context, Record<number, import("./entity-types").PostRevision>> | Record<import("./entity-types").Context, Record<number, import("./entity-types").GlobalStylesRevision>>) | Record<PropertyKey, never> | undefined) & import("rememo").EnhancedSelector;
}>>;
export { default as EntityProvider } from "./entity-provider";
export * from "./entity-provider";
export * from "./entity-types";
export * from "./fetch";
export * from "./hooks";
export * from "./private-apis";
import * as selectors from './selectors';
//# sourceMappingURL=index.d.ts.map