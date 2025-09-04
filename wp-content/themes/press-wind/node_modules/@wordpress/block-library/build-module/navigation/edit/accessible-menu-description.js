/**
 * WordPress dependencies
 */
import { useEntityProp } from '@wordpress/core-data';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import AccessibleDescription from './accessible-description';
import { jsx as _jsx } from "react/jsx-runtime";
export default function AccessibleMenuDescription({
  id
}) {
  const [menuTitle] = useEntityProp('postType', 'wp_navigation', 'title');
  /* translators: %s: Title of a Navigation Menu post. */
  const description = sprintf(__(`Navigation Menu: "%s"`), menuTitle);
  return /*#__PURE__*/_jsx(AccessibleDescription, {
    id: id,
    children: description
  });
}
//# sourceMappingURL=accessible-menu-description.js.map