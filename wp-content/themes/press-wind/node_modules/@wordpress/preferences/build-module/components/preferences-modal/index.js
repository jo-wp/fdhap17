/**
 * WordPress dependencies
 */
import { Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { jsx as _jsx } from "react/jsx-runtime";
export default function PreferencesModal({
  closeModal,
  children
}) {
  return /*#__PURE__*/_jsx(Modal, {
    className: "preferences-modal",
    title: __('Preferences'),
    onRequestClose: closeModal,
    children: children
  });
}
//# sourceMappingURL=index.js.map