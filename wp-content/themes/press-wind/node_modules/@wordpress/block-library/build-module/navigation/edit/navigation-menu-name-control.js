/**
 * WordPress dependencies
 */
import { TextControl } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { jsx as _jsx } from "react/jsx-runtime";
export default function NavigationMenuNameControl() {
  const [title, updateTitle] = useEntityProp('postType', 'wp_navigation', 'title');
  return /*#__PURE__*/_jsx(TextControl, {
    __next40pxDefaultSize: true,
    __nextHasNoMarginBottom: true,
    label: __('Menu name'),
    value: title,
    onChange: updateTitle
  });
}
//# sourceMappingURL=navigation-menu-name-control.js.map