/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { InnerBlocks } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
const deprecated = [{
  attributes: {
    verticalAlignment: {
      type: 'string'
    },
    width: {
      type: 'number',
      min: 0,
      max: 100
    }
  },
  isEligible({
    width
  }) {
    return isFinite(width);
  },
  migrate(attributes) {
    return {
      ...attributes,
      width: `${attributes.width}%`
    };
  },
  save({
    attributes
  }) {
    const {
      verticalAlignment,
      width
    } = attributes;
    const wrapperClasses = clsx({
      [`is-vertically-aligned-${verticalAlignment}`]: verticalAlignment
    });
    const style = {
      flexBasis: width + '%'
    };
    return /*#__PURE__*/_jsx("div", {
      className: wrapperClasses,
      style: style,
      children: /*#__PURE__*/_jsx(InnerBlocks.Content, {})
    });
  }
}];
export default deprecated;
//# sourceMappingURL=deprecated.js.map