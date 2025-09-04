/**
 * WordPress dependencies
 */
import { forwardRef } from '@wordpress/element';
import { jsx as _jsx } from "react/jsx-runtime";
function TagName(props, ref) {
  const {
    ordered,
    ...extraProps
  } = props;
  const Tag = ordered ? 'ol' : 'ul';
  return /*#__PURE__*/_jsx(Tag, {
    ref: ref,
    ...extraProps
  });
}
export default forwardRef(TagName);
//# sourceMappingURL=tag-name.js.map