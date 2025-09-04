/**
 * External dependencies
 */
import { Text, View } from 'react-native';

/**
 * WordPress dependencies
 */
import { usePreferredColorSchemeStyle } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import styles from './style.scss';
import { jsx as _jsx } from "react/jsx-runtime";
const HelpSectionTitle = ({
  children
}) => {
  const helpSectionTitle = usePreferredColorSchemeStyle(styles.helpSectionTitle, styles.helpSectionTitleDark);
  return /*#__PURE__*/_jsx(View, {
    style: styles.helpSectionTitleContainer,
    children: /*#__PURE__*/_jsx(Text, {
      style: helpSectionTitle,
      children: children
    })
  });
};
export default HelpSectionTitle;
//# sourceMappingURL=help-section-title.native.js.map