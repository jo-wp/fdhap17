/**
 * WordPress dependencies
 */
import { createSlotFill } from '@wordpress/components';

/**
 * Internal dependencies
 */
import EnablePanelOption from './enable-panel';
import { jsx as _jsx } from "react/jsx-runtime";
const {
  Fill,
  Slot
} = createSlotFill('EnablePluginDocumentSettingPanelOption');
const EnablePluginDocumentSettingPanelOption = ({
  label,
  panelName
}) => /*#__PURE__*/_jsx(Fill, {
  children: /*#__PURE__*/_jsx(EnablePanelOption, {
    label: label,
    panelName: panelName
  })
});
EnablePluginDocumentSettingPanelOption.Slot = Slot;
export default EnablePluginDocumentSettingPanelOption;
//# sourceMappingURL=enable-plugin-document-setting-panel.js.map