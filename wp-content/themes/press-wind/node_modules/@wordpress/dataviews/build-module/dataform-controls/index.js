/**
 * External dependencies
 */

/**
 * Internal dependencies
 */

import datetime from './datetime';
import integer from './integer';
import radio from './radio';
import select from './select';
import text from './text';
const FORM_CONTROLS = {
  datetime,
  integer,
  radio,
  select,
  text
};
export function getControl(field, fieldTypeDefinition) {
  if (typeof field.Edit === 'function') {
    return field.Edit;
  }
  if (typeof field.Edit === 'string') {
    return getControlByType(field.Edit);
  }
  if (field.elements) {
    return getControlByType('select');
  }
  if (typeof fieldTypeDefinition.Edit === 'string') {
    return getControlByType(fieldTypeDefinition.Edit);
  }
  return fieldTypeDefinition.Edit;
}
export function getControlByType(type) {
  if (Object.keys(FORM_CONTROLS).includes(type)) {
    return FORM_CONTROLS[type];
  }
  throw 'Control ' + type + ' not found';
}
//# sourceMappingURL=index.js.map