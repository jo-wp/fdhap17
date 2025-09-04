/**
 * WordPress dependencies
 */
import { __, _x } from '@wordpress/i18n';
import { RadioControl, __experimentalVStack as VStack } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { store as editorStore } from '../../store';
import { jsx as _jsx } from "react/jsx-runtime";
const COMMENT_OPTIONS = [{
  label: _x('Open', 'Adjective: e.g. "Comments are open"'),
  value: 'open',
  description: __('Visitors can add new comments and replies.')
}, {
  label: __('Closed'),
  value: 'closed',
  description: [__('Visitors cannot add new comments or replies.'), __('Existing comments remain visible.')].join(' ')
}];
function PostComments() {
  const commentStatus = useSelect(select => {
    var _select$getEditedPost;
    return (_select$getEditedPost = select(editorStore).getEditedPostAttribute('comment_status')) !== null && _select$getEditedPost !== void 0 ? _select$getEditedPost : 'open';
  }, []);
  const {
    editPost
  } = useDispatch(editorStore);
  const handleStatus = newCommentStatus => editPost({
    comment_status: newCommentStatus
  });
  return /*#__PURE__*/_jsx("form", {
    children: /*#__PURE__*/_jsx(VStack, {
      spacing: 4,
      children: /*#__PURE__*/_jsx(RadioControl, {
        className: "editor-change-status__options",
        hideLabelFromVision: true,
        label: __('Comment status'),
        options: COMMENT_OPTIONS,
        onChange: handleStatus,
        selected: commentStatus
      })
    })
  });
}

/**
 * A form for managing comment status.
 *
 * @return {JSX.Element} The rendered PostComments component.
 */
export default PostComments;
//# sourceMappingURL=index.js.map