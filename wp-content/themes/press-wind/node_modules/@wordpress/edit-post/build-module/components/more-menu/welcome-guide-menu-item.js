/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { PreferenceToggleMenuItem } from '@wordpress/preferences';
import { __ } from '@wordpress/i18n';
import { store as editorStore } from '@wordpress/editor';
import { jsx as _jsx } from "react/jsx-runtime";
export default function WelcomeGuideMenuItem() {
  const isEditingTemplate = useSelect(select => select(editorStore).getCurrentPostType() === 'wp_template', []);
  return /*#__PURE__*/_jsx(PreferenceToggleMenuItem, {
    scope: "core/edit-post",
    name: isEditingTemplate ? 'welcomeGuideTemplate' : 'welcomeGuide',
    label: __('Welcome Guide')
  });
}
//# sourceMappingURL=welcome-guide-menu-item.js.map