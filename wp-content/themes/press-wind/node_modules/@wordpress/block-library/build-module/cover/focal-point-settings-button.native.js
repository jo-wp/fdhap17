/**
 * External dependencies
 */
import { useNavigation } from '@react-navigation/native';
import { View } from 'react-native';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Icon, BottomSheet } from '@wordpress/components';
import { blockSettingsScreens } from '@wordpress/block-editor';
import { chevronRight } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import styles from './style.scss';
import { jsx as _jsx } from "react/jsx-runtime";
function FocalPointSettingsButton({
  disabled,
  focalPoint,
  onFocalPointChange,
  url
}) {
  const navigation = useNavigation();
  return /*#__PURE__*/_jsx(BottomSheet.Cell, {
    customActionButton: true,
    disabled: disabled,
    labelStyle: disabled && styles.dimmedActionButton,
    leftAlign: true,
    label: __('Edit focal point'),
    onPress: () => {
      navigation.navigate(blockSettingsScreens.focalPoint, {
        focalPoint,
        onFocalPointChange,
        url
      });
    },
    children: /*#__PURE__*/_jsx(View, {
      style: disabled && styles.dimmedActionButton,
      children: /*#__PURE__*/_jsx(Icon, {
        icon: chevronRight
      })
    })
  });
}
export default FocalPointSettingsButton;
//# sourceMappingURL=focal-point-settings-button.native.js.map