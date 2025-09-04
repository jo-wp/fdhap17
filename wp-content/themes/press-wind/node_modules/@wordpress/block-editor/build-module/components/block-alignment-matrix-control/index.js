/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { DOWN } from '@wordpress/keycodes';
import { ToolbarButton, Dropdown, AlignmentMatrixControl } from '@wordpress/components';
import { jsx as _jsx } from "react/jsx-runtime";
const noop = () => {};
function BlockAlignmentMatrixControl(props) {
  const {
    label = __('Change matrix alignment'),
    onChange = noop,
    value = 'center',
    isDisabled
  } = props;
  const icon = /*#__PURE__*/_jsx(AlignmentMatrixControl.Icon, {
    value: value
  });
  return /*#__PURE__*/_jsx(Dropdown, {
    popoverProps: {
      placement: 'bottom-start'
    },
    renderToggle: ({
      onToggle,
      isOpen
    }) => {
      const openOnArrowDown = event => {
        if (!isOpen && event.keyCode === DOWN) {
          event.preventDefault();
          onToggle();
        }
      };
      return /*#__PURE__*/_jsx(ToolbarButton, {
        onClick: onToggle,
        "aria-haspopup": "true",
        "aria-expanded": isOpen,
        onKeyDown: openOnArrowDown,
        label: label,
        icon: icon,
        showTooltip: true,
        disabled: isDisabled
      });
    },
    renderContent: () => /*#__PURE__*/_jsx(AlignmentMatrixControl, {
      hasFocusBorder: false,
      onChange: onChange,
      value: value
    })
  });
}
export default BlockAlignmentMatrixControl;
//# sourceMappingURL=index.js.map