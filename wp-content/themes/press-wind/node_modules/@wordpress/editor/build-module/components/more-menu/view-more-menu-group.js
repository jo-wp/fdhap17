/**
 * WordPress dependencies
 */
import { createSlotFill } from '@wordpress/components';
import { Platform } from '@wordpress/element';
import { jsx as _jsx } from "react/jsx-runtime";
const {
  Fill: ViewMoreMenuGroup,
  Slot
} = createSlotFill(Platform.OS === 'web' ? Symbol('ViewMoreMenuGroup') : 'ViewMoreMenuGroup');
ViewMoreMenuGroup.Slot = ({
  fillProps
}) => /*#__PURE__*/_jsx(Slot, {
  fillProps: fillProps
});
export default ViewMoreMenuGroup;
//# sourceMappingURL=view-more-menu-group.js.map