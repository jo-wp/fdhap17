/**
 * WordPress dependencies
 */
import { forwardRef, useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */

import { DropdownMenuContext } from './context';
import { Text } from '../text';
import * as Styled from './styles';
import { jsx as _jsx } from "react/jsx-runtime";
export const DropdownMenuGroupLabel = forwardRef(function DropdownMenuGroup(props, ref) {
  const dropdownMenuContext = useContext(DropdownMenuContext);
  return /*#__PURE__*/_jsx(Styled.DropdownMenuGroupLabel, {
    ref: ref,
    render:
    /*#__PURE__*/
    // @ts-expect-error The `children` prop is passed
    _jsx(Text, {
      upperCase: true,
      variant: "muted",
      size: "11px",
      weight: 500,
      lineHeight: "16px"
    }),
    ...props,
    store: dropdownMenuContext?.store
  });
});
//# sourceMappingURL=group-label.js.map