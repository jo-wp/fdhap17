/**
 * WordPress dependencies
 */
import { DropdownMenu, MenuGroup, MenuItemsChoice, ToolbarGroup, ToolbarItem } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { moveTo } from '@wordpress/icons';
import { jsx as _jsx } from "react/jsx-runtime";
export default function MoveToWidgetArea({
  currentWidgetAreaId,
  widgetAreas,
  onSelect
}) {
  return /*#__PURE__*/_jsx(ToolbarGroup, {
    children: /*#__PURE__*/_jsx(ToolbarItem, {
      children: toggleProps => /*#__PURE__*/_jsx(DropdownMenu, {
        icon: moveTo,
        label: __('Move to widget area'),
        toggleProps: toggleProps,
        children: ({
          onClose
        }) => /*#__PURE__*/_jsx(MenuGroup, {
          label: __('Move to'),
          children: /*#__PURE__*/_jsx(MenuItemsChoice, {
            choices: widgetAreas.map(widgetArea => ({
              value: widgetArea.id,
              label: widgetArea.name,
              info: widgetArea.description
            })),
            value: currentWidgetAreaId,
            onSelect: value => {
              onSelect(value);
              onClose();
            }
          })
        })
      })
    })
  });
}
//# sourceMappingURL=index.js.map