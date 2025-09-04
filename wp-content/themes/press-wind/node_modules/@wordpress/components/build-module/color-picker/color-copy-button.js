/**
 * WordPress dependencies
 */
import { useCopyToClipboard } from '@wordpress/compose';
import { useState, useEffect, useRef } from '@wordpress/element';
import { copy } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { CopyButton } from './styles';
import Tooltip from '../tooltip';
import { jsx as _jsx } from "react/jsx-runtime";
export const ColorCopyButton = props => {
  const {
    color,
    colorType
  } = props;
  const [copiedColor, setCopiedColor] = useState(null);
  const copyTimerRef = useRef();
  const copyRef = useCopyToClipboard(() => {
    switch (colorType) {
      case 'hsl':
        {
          return color.toHslString();
        }
      case 'rgb':
        {
          return color.toRgbString();
        }
      default:
      case 'hex':
        {
          return color.toHex();
        }
    }
  }, () => {
    if (copyTimerRef.current) {
      clearTimeout(copyTimerRef.current);
    }
    setCopiedColor(color.toHex());
    copyTimerRef.current = setTimeout(() => {
      setCopiedColor(null);
      copyTimerRef.current = undefined;
    }, 3000);
  });
  useEffect(() => {
    // Clear copyTimerRef on component unmount.
    return () => {
      if (copyTimerRef.current) {
        clearTimeout(copyTimerRef.current);
      }
    };
  }, []);
  return /*#__PURE__*/_jsx(Tooltip, {
    delay: 0,
    hideOnClick: false,
    text: copiedColor === color.toHex() ? __('Copied!') : __('Copy'),
    children: /*#__PURE__*/_jsx(CopyButton, {
      size: "small",
      ref: copyRef,
      icon: copy,
      showTooltip: false
    })
  });
};
//# sourceMappingURL=color-copy-button.js.map