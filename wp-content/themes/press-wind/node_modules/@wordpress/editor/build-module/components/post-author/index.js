/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

/**
 * Internal dependencies
 */
import PostAuthorCombobox from './combobox';
import PostAuthorSelect from './select';
import { AUTHORS_QUERY } from './constants';
import { jsx as _jsx } from "react/jsx-runtime";
const minimumUsersForCombobox = 25;

/**
 * Renders the component for selecting the post author.
 *
 * @return {Component} The component to be rendered.
 */
function PostAuthor() {
  const showCombobox = useSelect(select => {
    const authors = select(coreStore).getUsers(AUTHORS_QUERY);
    return authors?.length >= minimumUsersForCombobox;
  }, []);
  if (showCombobox) {
    return /*#__PURE__*/_jsx(PostAuthorCombobox, {});
  }
  return /*#__PURE__*/_jsx(PostAuthorSelect, {});
}
export default PostAuthor;
//# sourceMappingURL=index.js.map