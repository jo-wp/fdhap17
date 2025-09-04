/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { __experimentalVStack as VStack } from '@wordpress/components';
import { jsx as _jsx } from "react/jsx-runtime";
function PostPanelSection({
  className,
  children
}) {
  return /*#__PURE__*/_jsx(VStack, {
    className: clsx('editor-post-panel__section', className),
    children: children
  });
}
export default PostPanelSection;
//# sourceMappingURL=index.js.map