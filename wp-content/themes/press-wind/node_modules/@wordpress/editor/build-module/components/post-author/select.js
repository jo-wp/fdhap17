/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { SelectControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { store as editorStore } from '../../store';
import { useAuthorsQuery } from './hook';
import { jsx as _jsx } from "react/jsx-runtime";
export default function PostAuthorSelect() {
  const {
    editPost
  } = useDispatch(editorStore);
  const {
    authorId,
    authorOptions
  } = useAuthorsQuery();
  const setAuthorId = value => {
    const author = Number(value);
    editPost({
      author
    });
  };
  return /*#__PURE__*/_jsx(SelectControl, {
    __next40pxDefaultSize: true,
    __nextHasNoMarginBottom: true,
    className: "post-author-selector",
    label: __('Author'),
    options: authorOptions,
    onChange: setAuthorId,
    value: authorId,
    hideLabelFromVision: true
  });
}
//# sourceMappingURL=select.js.map