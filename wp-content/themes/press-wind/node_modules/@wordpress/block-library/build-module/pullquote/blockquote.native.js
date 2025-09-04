/**
 * External dependencies
 */
import { View } from 'react-native';
/**
 * WordPress dependencies
 */
import { Children, cloneElement } from '@wordpress/element';
/**
 * Internal dependencies
 */
import styles from './blockquote.scss';
import { jsx as _jsx } from "react/jsx-runtime";
export const BlockQuote = props => {
  const citationStyle = {
    ...styles.citation
  };
  const quoteStyle = {
    ...styles.quote
  };
  if (props.textColor) {
    quoteStyle.color = props.textColor;
    quoteStyle.placeholderColor = props.textColor;
    citationStyle.color = props.textColor;
    citationStyle.placeholderColor = props.textColor;
  }
  const newChildren = Children.map(props.children, child => {
    if (child && child.props.identifier === 'value') {
      return cloneElement(child, {
        style: quoteStyle
      });
    }
    if (child && child.props.identifier === 'citation') {
      return cloneElement(child, {
        style: citationStyle
      });
    }
    return child;
  });
  return /*#__PURE__*/_jsx(View, {
    children: newChildren
  });
};
//# sourceMappingURL=blockquote.native.js.map