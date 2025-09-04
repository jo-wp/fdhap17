/**
 * Returns true if two elements are contained within the same block.
 *
 * @param {Element} a First element.
 * @param {Element} b Second element.
 *
 * @return {boolean} Whether elements are in the same block.
 */
export function isInSameBlock(a: Element, b: Element): boolean;
/**
 * Returns true if an element is considered part of the block and not its inner
 * blocks or appender.
 *
 * @param {Element} blockElement Block container element.
 * @param {Element} element      Element.
 *
 * @return {boolean} Whether an element is considered part of the block and not
 *                   its inner blocks or appender.
 */
export function isInsideRootBlock(blockElement: Element, element: Element): boolean;
/**
 * Finds the block client ID given any DOM node inside the block.
 *
 * @param {Node?} node DOM node.
 *
 * @return {string|undefined} Client ID or undefined if the node is not part of
 *                            a block.
 */
export function getBlockClientId(node: Node | null): string | undefined;
/**
 * Calculates the union of two rectangles.
 *
 * @param {DOMRect} rect1 First rectangle.
 * @param {DOMRect} rect2 Second rectangle.
 * @return {DOMRect} Union of the two rectangles.
 */
export function rectUnion(rect1: DOMRect, rect2: DOMRect): DOMRect;
/**
 * Returns the rect of the element including all visible nested elements.
 *
 * Visible nested elements, including elements that overflow the parent, are
 * taken into account.
 *
 * This function is useful for calculating the visible area of a block that
 * contains nested elements that overflow the block, e.g. the Navigation block,
 * which can contain overflowing Submenu blocks.
 *
 * The returned rect represents the full extent of the element and its visible
 * children, which may extend beyond the viewport.
 *
 * @param {Element} element Element.
 * @return {DOMRect} Bounding client rect of the element and its visible children.
 */
export function getVisibleElementBounds(element: Element): DOMRect;
//# sourceMappingURL=dom.d.ts.map