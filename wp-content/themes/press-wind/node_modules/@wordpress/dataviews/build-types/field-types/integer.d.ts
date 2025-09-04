/**
 * Internal dependencies
 */
import type { SortDirection, ValidationContext } from '../types';
declare function sort(a: any, b: any, direction: SortDirection): number;
declare function isValid(value: any, context?: ValidationContext): boolean;
declare const _default: {
    sort: typeof sort;
    isValid: typeof isValid;
    Edit: string;
};
export default _default;
//# sourceMappingURL=integer.d.ts.map