/**
 * Checks if the current theme supports specific features and renders the children if supported.
 *
 * @param {Object}          props             The component props.
 * @param {Element}         props.children    The children to render if the theme supports the specified features.
 * @param {string|string[]} props.supportKeys The key(s) of the theme support(s) to check.
 *
 * @return {JSX.Element|null} The rendered children if the theme supports the specified features, otherwise null.
 */
export default function ThemeSupportCheck({ children, supportKeys }: {
    children: Element;
    supportKeys: string | string[];
}): JSX.Element | null;
//# sourceMappingURL=index.d.ts.map