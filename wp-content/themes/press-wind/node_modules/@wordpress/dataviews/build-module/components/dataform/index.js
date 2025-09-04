/**
 * Internal dependencies
 */

import { getFormLayout } from '../../dataforms-layouts';
import { jsx as _jsx } from "react/jsx-runtime";
export default function DataForm({
  form,
  ...props
}) {
  var _form$type;
  const layout = getFormLayout((_form$type = form.type) !== null && _form$type !== void 0 ? _form$type : 'regular');
  if (!layout) {
    return null;
  }
  return /*#__PURE__*/_jsx(layout.component, {
    form: form,
    ...props
  });
}
//# sourceMappingURL=index.js.map