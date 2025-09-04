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
export function PostPendingStatusCheck({ children }: {
    children: Element;
}): JSX.Element | null;
export default PostPendingStatusCheck;
//# sourceMappingURL=check.d.ts.map