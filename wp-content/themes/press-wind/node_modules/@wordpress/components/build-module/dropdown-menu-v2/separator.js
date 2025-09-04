/**
 * WordPress dependencies
 */
import { forwardRef, useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */

import { DropdownMenuContext } from './context';
import * as Styled from './styles';
import { jsx as _jsx } from "react/jsx-runtime";
export const DropdownMenuSeparator = forwardRef(function DropdownMenuSeparator(props, ref) {
  const dropdownMenuContext = useContext(DropdownMenuContext);
  return /*#__PURE__*/_jsx(Styled.DropdownMenuSeparator, {
    ref: ref,
    ...props,
    store: dropdownMenuContext?.store,
    variant: dropdownMenuContext?.variant
  });
});
//# sourceMappingURL=separator.js.map