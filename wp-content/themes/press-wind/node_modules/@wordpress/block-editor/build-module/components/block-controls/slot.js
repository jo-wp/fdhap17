/**
 * WordPress dependencies
 */
import { useContext, useMemo } from '@wordpress/element';
import { privateApis, __experimentalToolbarContext as ToolbarContext, ToolbarGroup, __experimentalUseSlotFills as useSlotFills } from '@wordpress/components';
import warning from '@wordpress/warning';

/**
 * Internal dependencies
 */
import groups from './groups';
import { unlock } from '../../lock-unlock';
import { jsx as _jsx } from "react/jsx-runtime";
const {
  ComponentsContext
} = unlock(privateApis);
export default function BlockControlsSlot({
  group = 'default',
  ...props
}) {
  const toolbarState = useContext(ToolbarContext);
  const contextState = useContext(ComponentsContext);
  const fillProps = useMemo(() => ({
    forwardedContext: [[ToolbarContext.Provider, {
      value: toolbarState
    }], [ComponentsContext.Provider, {
      value: contextState
    }]]
  }), [toolbarState, contextState]);
  const Slot = groups[group]?.Slot;
  const fills = useSlotFills(Slot?.__unstableName);
  if (!Slot) {
    globalThis.SCRIPT_DEBUG === true ? warning(`Unknown BlockControls group "${group}" provided.`) : void 0;
    return null;
  }
  if (!fills?.length) {
    return null;
  }
  const slot = /*#__PURE__*/_jsx(Slot, {
    ...props,
    bubblesVirtually: true,
    fillProps: fillProps
  });
  if (group === 'default') {
    return slot;
  }
  return /*#__PURE__*/_jsx(ToolbarGroup, {
    children: slot
  });
}
//# sourceMappingURL=slot.js.map