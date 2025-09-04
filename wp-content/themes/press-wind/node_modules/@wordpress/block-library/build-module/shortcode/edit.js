/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { PlainText, useBlockProps } from '@wordpress/block-editor';
import { useInstanceId } from '@wordpress/compose';
import { Placeholder } from '@wordpress/components';
import { shortcode } from '@wordpress/icons';
import { jsx as _jsx } from "react/jsx-runtime";
export default function ShortcodeEdit({
  attributes,
  setAttributes
}) {
  const instanceId = useInstanceId(ShortcodeEdit);
  const inputId = `blocks-shortcode-input-${instanceId}`;
  return /*#__PURE__*/_jsx("div", {
    ...useBlockProps(),
    children: /*#__PURE__*/_jsx(Placeholder, {
      icon: shortcode,
      label: __('Shortcode'),
      children: /*#__PURE__*/_jsx(PlainText, {
        className: "blocks-shortcode__textarea",
        id: inputId,
        value: attributes.text,
        "aria-label": __('Shortcode text'),
        placeholder: __('Write shortcode here…'),
        onChange: text => setAttributes({
          text
        })
      })
    })
  });
}
//# sourceMappingURL=edit.js.map