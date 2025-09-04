/**
 * Determines if the current post can be edited (published)
 * and passes this information to the provided render function.
 *
 * @param {Object}   props        The component props.
 * @param {Function} props.render Function to render the component.
 *                                Receives an object with a `canEdit` property.
 * @return {JSX.Element} The rendered component.
 */
export default function PostVisibilityCheck({ render }: {
    render: Function;
}): JSX.Element;
//# sourceMappingURL=check.d.ts.map