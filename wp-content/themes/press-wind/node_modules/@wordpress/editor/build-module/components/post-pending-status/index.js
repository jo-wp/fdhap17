/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { CheckboxControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import PostPendingStatusCheck from './check';
import { store as editorStore } from '../../store';

/**
 * A component for displaying and toggling the pending status of a post.
 *
 * @return {JSX.Element} The rendered component.
 */
import { jsx as _jsx } from "react/jsx-runtime";
export function PostPendingStatus() {
  const status = useSelect(select => select(editorStore).getEditedPostAttribute('status'), []);
  const {
    editPost
  } = useDispatch(editorStore);
  const togglePendingStatus = () => {
    const updatedStatus = status === 'pending' ? 'draft' : 'pending';
    editPost({
      status: updatedStatus
    });
  };
  return /*#__PURE__*/_jsx(PostPendingStatusCheck, {
    children: /*#__PURE__*/_jsx(CheckboxControl, {
      __nextHasNoMarginBottom: true,
      label: __('Pending review'),
      checked: status === 'pending',
      onChange: togglePendingStatus
    })
  });
}
export default PostPendingStatus;
//# sourceMappingURL=index.js.map