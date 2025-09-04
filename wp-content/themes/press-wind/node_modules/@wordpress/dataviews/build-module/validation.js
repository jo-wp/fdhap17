/**
 * Internal dependencies
 */
import { normalizeFields } from './normalize-fields';
export function isItemValid(item, fields, form) {
  const _fields = normalizeFields(fields.filter(({
    id
  }) => !!form.fields?.includes(id)));
  return _fields.every(field => {
    return field.isValid(item, {
      elements: field.elements
    });
  });
}
//# sourceMappingURL=validation.js.map