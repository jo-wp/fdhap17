/**
 * Wrapper component that renders its children only if the post type supports page attributes.
 *
 * @param {Object}  props          - The component props.
 * @param {Element} props.children - The child components to render.
 *
 * @return {Component|null} The rendered child components or null if page attributes are not supported.
 */
export function PageAttributesCheck({ children }: {
    children: Element;
}): Component | null;
export default PageAttributesCheck;
//# sourceMappingURL=check.d.ts.map