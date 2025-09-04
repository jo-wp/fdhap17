/**
 * WordPress dependencies
 */
import { __experimentalText as Text } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';
import { humanTimeDiff } from '@wordpress/date';

/**
 * Internal dependencies
 */
import { store as editorStore } from '../../store';
import { jsx as _jsx } from "react/jsx-runtime";
export default function PostLastEditedPanel() {
  const modified = useSelect(select => select(editorStore).getEditedPostAttribute('modified'), []);
  const lastEditedText = modified && sprintf(
  // translators: %s: Human-readable time difference, e.g. "2 days ago".
  __('Last edited %s.'), humanTimeDiff(modified));
  if (!lastEditedText) {
    return null;
  }
  return /*#__PURE__*/_jsx("div", {
    className: "editor-post-last-edited-panel",
    children: /*#__PURE__*/_jsx(Text, {
      children: lastEditedText
    })
  });
}
//# sourceMappingURL=index.js.map