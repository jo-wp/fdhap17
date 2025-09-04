/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { store as editorStore } from '../../store';

/**
 * This component checks the publishing status of the current post.
 * If the post is already published or the user doesn't have the
 * capability to publish, it returns null.
 *
 * @param {Object}  props          Component properties.
 * @param {Element} props.children Children to be rendered.
 *
 * @return {JSX.Element|null} The rendered child elements or null if the post is already published or the user doesn't have the capability to publish.
 */
export function PostPendingStatusCheck({
  children
}) {
  const {
    hasPublishAction,
    isPublished
  } = useSelect(select => {
    var _getCurrentPost$_link;
    const {
      isCurrentPostPublished,
      getCurrentPost
    } = select(editorStore);
    return {
      hasPublishAction: (_getCurrentPost$_link = getCurrentPost()._links?.['wp:action-publish']) !== null && _getCurrentPost$_link !== void 0 ? _getCurrentPost$_link : false,
      isPublished: isCurrentPostPublished()
    };
  }, []);
  if (isPublished || !hasPublishAction) {
    return null;
  }
  return children;
}
export default PostPendingStatusCheck;
//# sourceMappingURL=check.js.map