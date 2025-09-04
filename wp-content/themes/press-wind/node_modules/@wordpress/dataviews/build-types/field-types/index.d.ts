/**
 * Internal dependencies
 */
import type { FieldType, SortDirection, ValidationContext } from '../types';
/**
 *
 * @param {FieldType} type The field type definition to get.
 *
 * @return A field type definition.
 */
export default function getFieldTypeDefinition(type?: FieldType): {
    sort: (valueA: any, valueB: any, direction: SortDirection) => any;
    isValid: (value: any, context?: ValidationContext) => boolean;
    Edit: string;
} | {
    sort: (a: any, b: any, direction: SortDirection) => any;
    isValid: (value: any, context?: ValidationContext) => boolean;
    Edit: () => null;
};
//# sourceMappingURL=index.d.ts.map