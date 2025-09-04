/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { CheckboxControl, ExternalLink } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { store as editorStore } from '../../store';
import { jsx as _jsx } from "react/jsx-runtime";
function PostPingbacks() {
  const pingStatus = useSelect(select => {
    var _select$getEditedPost;
    return (_select$getEditedPost = select(editorStore).getEditedPostAttribute('ping_status')) !== null && _select$getEditedPost !== void 0 ? _select$getEditedPost : 'open';
  }, []);
  const {
    editPost
  } = useDispatch(editorStore);
  const onTogglePingback = () => editPost({
    ping_status: pingStatus === 'open' ? 'closed' : 'open'
  });
  return /*#__PURE__*/_jsx(CheckboxControl, {
    __nextHasNoMarginBottom: true,
    label: __('Enable pingbacks & trackbacks'),
    checked: pingStatus === 'open',
    onChange: onTogglePingback,
    help: /*#__PURE__*/_jsx(ExternalLink, {
      href: __('https://wordpress.org/documentation/article/trackbacks-and-pingbacks/'),
      children: __('Learn more about pingbacks & trackbacks')
    })
  });
}

/**
 * Renders a control for enabling or disabling pingbacks and trackbacks
 * in a WordPress post.
 *
 * @module PostPingbacks
 */
export default PostPingbacks;
//# sourceMappingURL=index.js.map