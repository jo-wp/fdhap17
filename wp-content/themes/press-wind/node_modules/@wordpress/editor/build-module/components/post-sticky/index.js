/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { CheckboxControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import PostStickyCheck from './check';
import { store as editorStore } from '../../store';

/**
 * Renders the PostSticky component. It provides a checkbox control for the sticky post feature.
 *
 * @return {Component} The component to be rendered.
 */
import { jsx as _jsx } from "react/jsx-runtime";
export default function PostSticky() {
  const postSticky = useSelect(select => {
    var _select$getEditedPost;
    return (_select$getEditedPost = select(editorStore).getEditedPostAttribute('sticky')) !== null && _select$getEditedPost !== void 0 ? _select$getEditedPost : false;
  }, []);
  const {
    editPost
  } = useDispatch(editorStore);
  return /*#__PURE__*/_jsx(PostStickyCheck, {
    children: /*#__PURE__*/_jsx(CheckboxControl, {
      className: "editor-post-sticky__checkbox-control",
      label: __('Sticky'),
      help: __('Pin this post to the top of the blog'),
      checked: postSticky,
      onChange: () => editPost({
        sticky: !postSticky
      }),
      __nextHasNoMarginBottom: true
    })
  });
}
//# sourceMappingURL=index.js.map