/**
 * WordPress dependencies
 */
import { forwardRef } from '@wordpress/element';

/**
 * Internal dependencies
 */

import * as Styled from './styles';
import { jsx as _jsx } from "react/jsx-runtime";
export const DropdownMenuItemLabel = forwardRef(function DropdownMenuItemLabel(props, ref) {
  return /*#__PURE__*/_jsx(Styled.DropdownMenuItemLabel, {
    numberOfLines: 1,
    ref: ref,
    ...props
  });
});
//# sourceMappingURL=item-label.js.map