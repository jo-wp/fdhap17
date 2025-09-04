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
export const DropdownMenuGroup = forwardRef(function DropdownMenuGroup(props, ref) {
  const dropdownMenuContext = useContext(DropdownMenuContext);
  return /*#__PURE__*/_jsx(Styled.DropdownMenuGroup, {
    ref: ref,
    ...props,
    store: dropdownMenuContext?.store
  });
});
//# sourceMappingURL=group.js.map