/**
 * WordPress dependencies
 */
import { RadioControl } from '@wordpress/components';
import { useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { jsx as _jsx } from "react/jsx-runtime";
export default function Radio({
  data,
  field,
  onChange,
  hideLabelFromVision
}) {
  const {
    id,
    label
  } = field;
  const value = field.getValue({
    item: data
  });
  const onChangeControl = useCallback(newValue => onChange({
    [id]: newValue
  }), [id, onChange]);
  if (field.elements) {
    return /*#__PURE__*/_jsx(RadioControl, {
      label: label,
      onChange: onChangeControl,
      options: field.elements,
      selected: value,
      hideLabelFromVision: hideLabelFromVision
    });
  }
  return null;
}
//# sourceMappingURL=radio.js.map