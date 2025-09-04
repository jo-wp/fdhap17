/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import PostTypeSupportCheck from '../post-type-support-check';
import { store as editorStore } from '../../store';

/**
 * Wrapper component that renders its children if the post has more than one revision.
 *
 * @param {Object}  props          Props.
 * @param {Element} props.children Children to be rendered.
 *
 * @return {Component|null} Rendered child components if post has more than one revision, otherwise null.
 */
import { jsx as _jsx } from "react/jsx-runtime";
function PostLastRevisionCheck({
  children
}) {
  const {
    lastRevisionId,
    revisionsCount
  } = useSelect(select => {
    const {
      getCurrentPostLastRevisionId,
      getCurrentPostRevisionsCount
    } = select(editorStore);
    return {
      lastRevisionId: getCurrentPostLastRevisionId(),
      revisionsCount: getCurrentPostRevisionsCount()
    };
  }, []);
  if (!lastRevisionId || revisionsCount < 2) {
    return null;
  }
  return /*#__PURE__*/_jsx(PostTypeSupportCheck, {
    supportKeys: "revisions",
    children: children
  });
}
export default PostLastRevisionCheck;
//# sourceMappingURL=check.js.map