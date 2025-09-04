/**
 * External dependencies
 */
import { useNavigation } from '@react-navigation/native';

/**
 * WordPress dependencies
 */
import { TextControl, Icon } from '@wordpress/components';
import { chevronRight } from '@wordpress/icons';
import { jsx as _jsx } from "react/jsx-runtime";
const HelpTopicRow = ({
  label,
  icon,
  screenName,
  isLastItem
}) => {
  const navigation = useNavigation();
  const openSubSheet = () => {
    navigation.navigate(screenName);
  };
  return /*#__PURE__*/_jsx(TextControl, {
    separatorType: isLastItem ? 'none' : 'leftMargin',
    customActionButton: true,
    leftAlign: true,
    onPress: openSubSheet,
    label: label,
    icon: icon,
    children: /*#__PURE__*/_jsx(Icon, {
      icon: chevronRight
    })
  });
};
export default HelpTopicRow;
//# sourceMappingURL=help-topic-row.native.js.map