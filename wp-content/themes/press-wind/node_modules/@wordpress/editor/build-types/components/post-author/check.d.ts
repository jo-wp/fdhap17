/**
 * Wrapper component that renders its children only if the post type supports the author.
 *
 * @param {Object}  props          The component props.
 * @param {Element} props.children Children to be rendered.
 *
 * @return {Component|null} The component to be rendered. Return `null` if the post type doesn't
 * supports the author or if there are no authors available.
 */
export default function PostAuthorCheck({ children }: {
    children: Element;
}): Component | null;
//# sourceMappingURL=check.d.ts.map