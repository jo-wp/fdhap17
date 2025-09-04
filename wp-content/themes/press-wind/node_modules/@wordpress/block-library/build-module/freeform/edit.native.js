/**
 * Internal dependencies
 */
import MissingEdit from '../missing/edit';
import { jsx as _jsx } from "react/jsx-runtime";
const ClassicEdit = props => /*#__PURE__*/_jsx(MissingEdit, {
  ...props,
  attributes: {
    ...props.attributes,
    originalName: props.name
  }
});
export default ClassicEdit;
//# sourceMappingURL=edit.native.js.map