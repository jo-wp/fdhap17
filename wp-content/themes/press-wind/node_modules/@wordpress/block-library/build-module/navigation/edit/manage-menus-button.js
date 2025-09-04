/**
 * WordPress dependencies
 */
import { addQueryArgs } from '@wordpress/url';
import { Button, MenuItem } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { jsx as _jsx } from "react/jsx-runtime";
const ManageMenusButton = ({
  className = '',
  disabled,
  isMenuItem = false
}) => {
  let ComponentName = Button;
  if (isMenuItem) {
    ComponentName = MenuItem;
  }
  return /*#__PURE__*/_jsx(ComponentName, {
    variant: "link",
    disabled: disabled,
    className: className,
    href: addQueryArgs('edit.php', {
      post_type: 'wp_navigation'
    }),
    children: __('Manage menus')
  });
};
export default ManageMenusButton;
//# sourceMappingURL=manage-menus-button.js.map