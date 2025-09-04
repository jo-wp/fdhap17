/**
 * WordPress dependencies
 */
import { privateApis as editorPrivateApis } from '@wordpress/editor';
import { __unstableMotion as motion } from '@wordpress/components';

/**
 * Internal dependencies
 */
import FullscreenModeClose from './fullscreen-mode-close';
import { unlock } from '../../lock-unlock';
import { jsx as _jsx } from "react/jsx-runtime";
const {
  BackButton: BackButtonFill
} = unlock(editorPrivateApis);
const slideX = {
  hidden: {
    x: '-100%'
  },
  distractionFreeInactive: {
    x: 0
  },
  hover: {
    x: 0,
    transition: {
      type: 'tween',
      delay: 0.2
    }
  }
};
function BackButton({
  initialPost
}) {
  return /*#__PURE__*/_jsx(BackButtonFill, {
    children: ({
      length
    }) => length <= 1 && /*#__PURE__*/_jsx(motion.div, {
      variants: slideX,
      transition: {
        type: 'tween',
        delay: 0.8
      },
      children: /*#__PURE__*/_jsx(FullscreenModeClose, {
        showTooltip: true,
        initialPost: initialPost
      })
    })
  });
}
export default BackButton;
//# sourceMappingURL=index.js.map