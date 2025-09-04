/**
 * WordPress dependencies
 */
import { RawHTML } from '@wordpress/element';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save({
  attributes: {
    customText,
    noTeaser
  }
}) {
  const moreTag = customText ? `<!--more ${customText}-->` : '<!--more-->';
  const noTeaserTag = noTeaser ? '<!--noteaser-->' : '';
  return /*#__PURE__*/_jsx(RawHTML, {
    children: [moreTag, noTeaserTag].filter(Boolean).join('\n')
  });
}
//# sourceMappingURL=save.js.map