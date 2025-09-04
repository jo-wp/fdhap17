/**
 * WordPress dependencies
 */
import { forwardRef } from '@wordpress/element';

/**
 * Internal dependencies
 */

import * as Styled from './styles';
import { jsx as _jsx } from "react/jsx-runtime";
export const DropdownMenuItemHelpText = forwardRef(function DropdownMenuItemHelpText(props, ref) {
  return /*#__PURE__*/_jsx(Styled.DropdownMenuItemHelpText, {
    numberOfLines: 2,
    ref: ref,
    ...props
  });
});
//# sourceMappingURL=item-help-text.js.map