/**
 * External dependencies
 */
import type { ComponentType } from 'react';
/**
 * Internal dependencies
 */
import type { DataFormControlProps, Field, FieldTypeDefinition } from '../types';
export declare function getControl<Item>(field: Field<Item>, fieldTypeDefinition: FieldTypeDefinition<Item>): ComponentType<DataFormControlProps<any>> | ComponentType<DataFormControlProps<Item>>;
export declare function getControlByType(type: string): ComponentType<DataFormControlProps<any>>;
//# sourceMappingURL=index.d.ts.map