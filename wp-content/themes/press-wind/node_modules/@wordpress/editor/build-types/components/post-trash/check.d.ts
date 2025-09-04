/**
 * Wrapper component that renders its children only if the post can trashed.
 *
 * @param {Object}  props          - The component props.
 * @param {Element} props.children - The child components to render.
 *
 * @return {Component|null} The rendered child components or null if the post can not trashed.
 */
export default function PostTrashCheck({ children }: {
    children: Element;
}): Component | null;
//# sourceMappingURL=check.d.ts.map