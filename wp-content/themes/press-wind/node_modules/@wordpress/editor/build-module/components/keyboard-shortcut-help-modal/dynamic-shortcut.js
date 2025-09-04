/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { store as keyboardShortcutsStore } from '@wordpress/keyboard-shortcuts';

/**
 * Internal dependencies
 */
import Shortcut from './shortcut';
import { jsx as _jsx } from "react/jsx-runtime";
function DynamicShortcut({
  name
}) {
  const {
    keyCombination,
    description,
    aliases
  } = useSelect(select => {
    const {
      getShortcutKeyCombination,
      getShortcutDescription,
      getShortcutAliases
    } = select(keyboardShortcutsStore);
    return {
      keyCombination: getShortcutKeyCombination(name),
      aliases: getShortcutAliases(name),
      description: getShortcutDescription(name)
    };
  }, [name]);
  if (!keyCombination) {
    return null;
  }
  return /*#__PURE__*/_jsx(Shortcut, {
    keyCombination: keyCombination,
    description: description,
    aliases: aliases
  });
}
export default DynamicShortcut;
//# sourceMappingURL=dynamic-shortcut.js.map