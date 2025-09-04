/**
 * WordPress dependencies
 */
import { forwardRef } from '@wordpress/element';
import { View } from '@wordpress/primitives';
import { jsx as _jsx } from "react/jsx-runtime";
function TagName(props, ref) {
  const {
    start,
    ...extraProps
  } = props;
  return /*#__PURE__*/_jsx(View, {
    ref: ref,
    ...extraProps
  });
}
export default forwardRef(TagName);
//# sourceMappingURL=tag-name.native.js.map