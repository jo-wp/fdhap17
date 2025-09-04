/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { store as preferencesStore } from '../../store';
import PreferenceBaseOption from '../preference-base-option';
import { jsx as _jsx } from "react/jsx-runtime";
function PreferenceToggleControl(props) {
  const {
    scope,
    featureName,
    onToggle = () => {},
    ...remainingProps
  } = props;
  const isChecked = useSelect(select => !!select(preferencesStore).get(scope, featureName), [scope, featureName]);
  const {
    toggle
  } = useDispatch(preferencesStore);
  const onChange = () => {
    onToggle();
    toggle(scope, featureName);
  };
  return /*#__PURE__*/_jsx(PreferenceBaseOption, {
    onChange: onChange,
    isChecked: isChecked,
    ...remainingProps
  });
}
export default PreferenceToggleControl;
//# sourceMappingURL=index.js.map