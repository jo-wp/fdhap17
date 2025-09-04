/**
 * External dependencies
 */
import { ActivityIndicator, View } from 'react-native';

/**
 * WordPress dependencies
 */
import { usePreferredColorSchemeStyle } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import styles from './styles.scss';
import { jsx as _jsx } from "react/jsx-runtime";
const EmbedLoading = () => {
  const style = usePreferredColorSchemeStyle(styles['embed-preview__loading'], styles['embed-preview__loading--dark']);
  return /*#__PURE__*/_jsx(View, {
    style: style,
    children: /*#__PURE__*/_jsx(ActivityIndicator, {
      animating: true
    })
  });
};
export default EmbedLoading;
//# sourceMappingURL=embed-loading.native.js.map