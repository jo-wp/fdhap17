/**
 * External dependencies
 */
import { Pressable, Text } from 'react-native';

/**
 * WordPress dependencies
 */
import { usePreferredColorSchemeStyle } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import styles from './style.scss';
import { jsx as _jsx } from "react/jsx-runtime";
const HelpGetSupportButton = ({
  onPress,
  title
}) => {
  const buttonStyle = usePreferredColorSchemeStyle(styles.button, styles.buttonDark);
  const textStyle = usePreferredColorSchemeStyle(styles.buttonText, styles.buttonTextDark);
  return /*#__PURE__*/_jsx(Pressable, {
    style: buttonStyle,
    onPress: onPress,
    accessibilityRole: "button",
    children: /*#__PURE__*/_jsx(Text, {
      style: textStyle,
      children: title
    })
  });
};
export default HelpGetSupportButton;
//# sourceMappingURL=help-get-support-button.native.js.map