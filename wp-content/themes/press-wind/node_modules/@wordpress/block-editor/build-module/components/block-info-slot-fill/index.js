/**
 * WordPress dependencies
 */
import { privateApis as componentsPrivateApis } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { unlock } from '../../lock-unlock';
import { useBlockEditContext, mayDisplayControlsKey } from '../block-edit/context';
import { jsx as _jsx } from "react/jsx-runtime";
const {
  createPrivateSlotFill
} = unlock(componentsPrivateApis);
const {
  Fill,
  Slot
} = createPrivateSlotFill('BlockInformation');
const BlockInfo = props => {
  const context = useBlockEditContext();
  if (!context[mayDisplayControlsKey]) {
    return null;
  }
  return /*#__PURE__*/_jsx(Fill, {
    ...props
  });
};
BlockInfo.Slot = props => /*#__PURE__*/_jsx(Slot, {
  ...props
});
export default BlockInfo;
//# sourceMappingURL=index.js.map