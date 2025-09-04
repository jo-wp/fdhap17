/**
 * External dependencies
 */
import { View } from 'react-native';

/**
 * WordPress dependencies
 */
import { usePreferredColorSchemeStyle } from '@wordpress/compose';
import { useConvertUnitToMobile } from '@wordpress/components';

/**
 * Internal dependencies
 */
import styles from './editor.scss';
import { jsx as _jsx } from "react/jsx-runtime";
function ColumnPreviewItem({
  index,
  selectedColumnIndex,
  width
}) {
  const columnIndicatorStyle = usePreferredColorSchemeStyle(styles.columnIndicator, styles.columnIndicatorDark);
  const isSelectedColumn = index === selectedColumnIndex;
  const convertedWidth = useConvertUnitToMobile(width);
  return /*#__PURE__*/_jsx(View, {
    style: [isSelectedColumn && columnIndicatorStyle, {
      flex: convertedWidth
    }]
  }, index);
}
function ColumnsPreview({
  columnWidths,
  selectedColumnIndex
}) {
  const columnsPreviewStyle = usePreferredColorSchemeStyle(styles.columnsPreview, styles.columnsPreviewDark);
  return /*#__PURE__*/_jsx(View, {
    style: columnsPreviewStyle,
    children: columnWidths.map((width, index) => {
      return /*#__PURE__*/_jsx(ColumnPreviewItem, {
        index: index,
        selectedColumnIndex: selectedColumnIndex,
        width: width
      }, index);
    })
  });
}
export default ColumnsPreview;
//# sourceMappingURL=column-preview.native.js.map