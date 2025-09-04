/**
 * Internal dependencies
 */
import type { Field, Action } from '../../../types';
export type Theme = {
    slug: string;
    name: string;
    description: string;
    requires: string;
    tested: string;
    tags: string[];
};
export type SpaceObject = {
    id: number;
    title: string;
    description: string;
    image: string;
    type: string;
    categories: string[];
    satellites: number;
    date: string;
};
export declare const data: SpaceObject[];
export declare const themeData: Theme[];
export declare const themeFields: Field<Theme>[];
export declare const DEFAULT_VIEW: {
    type: "table";
    search: string;
    page: number;
    perPage: number;
    layout: {};
    filters: never[];
};
export declare const actions: Action<SpaceObject>[];
export declare const fields: Field<SpaceObject>[];
//# sourceMappingURL=fixtures.d.ts.map