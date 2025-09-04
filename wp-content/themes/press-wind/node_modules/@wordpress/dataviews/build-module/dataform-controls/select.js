/**
 * WordPress dependencies
 */
import { SelectControl } from '@wordpress/components';
import { useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { jsx as _jsx } from "react/jsx-runtime";
export default function Select({
  data,
  field,
  onChange,
  hideLabelFromVision
}) {
  var _field$getValue, _field$elements;
  const {
    id,
    label
  } = field;
  const value = (_field$getValue = field.getValue({
    item: data
  })) !== null && _field$getValue !== void 0 ? _field$getValue : '';
  const onChangeControl = useCallback(newValue => onChange({
    [id]: newValue
  }), [id, onChange]);
  const elements = [
  /*
   * Value can be undefined when:
   *
   * - the field is not required
   * - in bulk editing
   *
   */
  {
    label: __('Select item'),
    value: ''
  }, ...((_field$elements = field?.elements) !== null && _field$elements !== void 0 ? _field$elements : [])];
  return /*#__PURE__*/_jsx(SelectControl, {
    label: label,
    value: value,
    options: elements,
    onChange: onChangeControl,
    __next40pxDefaultSize: true,
    __nextHasNoMarginBottom: true,
    hideLabelFromVision: hideLabelFromVision
  });
}
//# sourceMappingURL=select.js.map