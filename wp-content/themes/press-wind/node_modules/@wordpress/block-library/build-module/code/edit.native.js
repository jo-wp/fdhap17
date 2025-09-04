/**
 * External dependencies
 */
import { View } from 'react-native';

/**
 * WordPress dependencies
 */
import { RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { usePreferredColorSchemeStyle } from '@wordpress/compose';
import { createBlock, getDefaultBlockName } from '@wordpress/blocks';

/**
 * Internal dependencies
 */

/**
 * Block code style
 */
import styles from './theme.scss';
import { jsx as _jsx } from "react/jsx-runtime";
export function CodeEdit(props) {
  const {
    attributes,
    setAttributes,
    onRemove,
    style,
    insertBlocksAfter,
    mergeBlocks
  } = props;
  const codeStyle = {
    ...usePreferredColorSchemeStyle(styles.blockCode, styles.blockCodeDark)
  };
  const textStyle = style?.fontSize ? {
    fontSize: style.fontSize
  } : {};
  const placeholderStyle = usePreferredColorSchemeStyle(styles.placeholder, styles.placeholderDark);
  return /*#__PURE__*/_jsx(View, {
    style: codeStyle,
    children: /*#__PURE__*/_jsx(RichText, {
      tagName: "pre",
      value: attributes.content,
      identifier: "content",
      style: textStyle,
      underlineColorAndroid: "transparent",
      onChange: content => setAttributes({
        content
      }),
      onMerge: mergeBlocks,
      onRemove: onRemove,
      placeholder: __('Write code…'),
      "aria-label": __('Code'),
      placeholderTextColor: placeholderStyle.color,
      preserveWhiteSpace: true,
      __unstablePastePlainText: true,
      __unstableOnSplitAtDoubleLineEnd: () => insertBlocksAfter(createBlock(getDefaultBlockName()))
    })
  });
}
export default CodeEdit;
//# sourceMappingURL=edit.native.js.map