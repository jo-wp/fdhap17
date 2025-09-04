/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { store as editorStore } from '../../store';

/**
 * Wrapper component that renders its children only if post has a publish action.
 *
 * @param {Object}  props          Props.
 * @param {Element} props.children Children to be rendered.
 *
 * @return {Component} - The component to be rendered or null if there is no publish action.
 */
export default function PostScheduleCheck({
  children
}) {
  const hasPublishAction = useSelect(select => {
    var _select$getCurrentPos;
    return (_select$getCurrentPos = select(editorStore).getCurrentPost()._links?.['wp:action-publish']) !== null && _select$getCurrentPos !== void 0 ? _select$getCurrentPos : false;
  }, []);
  if (!hasPublishAction) {
    return null;
  }
  return children;
}
//# sourceMappingURL=check.js.map