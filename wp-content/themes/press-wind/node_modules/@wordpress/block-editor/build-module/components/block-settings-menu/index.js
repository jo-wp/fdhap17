/**
 * WordPress dependencies
 */
import { ToolbarGroup, ToolbarItem } from '@wordpress/components';

/**
 * Internal dependencies
 */
import BlockSettingsDropdown from './block-settings-dropdown';
import { jsx as _jsx } from "react/jsx-runtime";
export function BlockSettingsMenu({
  clientIds,
  ...props
}) {
  return /*#__PURE__*/_jsx(ToolbarGroup, {
    children: /*#__PURE__*/_jsx(ToolbarItem, {
      children: toggleProps => /*#__PURE__*/_jsx(BlockSettingsDropdown, {
        clientIds: clientIds,
        toggleProps: toggleProps,
        ...props
      })
    })
  });
}
export default BlockSettingsMenu;
//# sourceMappingURL=index.js.map