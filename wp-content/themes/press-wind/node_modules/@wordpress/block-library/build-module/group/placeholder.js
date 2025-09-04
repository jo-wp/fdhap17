/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { store as blocksStore } from '@wordpress/blocks';
import { Path, SVG, Button, Placeholder } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

/**
 * Returns a custom variation icon.
 *
 * @param {string} name The block variation name.
 *
 * @return {JSX.Element} The SVG element.
 */
import { jsx as _jsx } from "react/jsx-runtime";
const getGroupPlaceholderIcons = (name = 'group') => {
  const icons = {
    group: /*#__PURE__*/_jsx(SVG, {
      xmlns: "http://www.w3.org/2000/svg",
      width: "48",
      height: "48",
      viewBox: "0 0 48 48",
      children: /*#__PURE__*/_jsx(Path, {
        d: "M0 10a2 2 0 0 1 2-2h44a2 2 0 0 1 2 2v28a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V10Z"
      })
    }),
    'group-row': /*#__PURE__*/_jsx(SVG, {
      xmlns: "http://www.w3.org/2000/svg",
      width: "48",
      height: "48",
      viewBox: "0 0 48 48",
      children: /*#__PURE__*/_jsx(Path, {
        d: "M0 10a2 2 0 0 1 2-2h19a2 2 0 0 1 2 2v28a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V10Zm25 0a2 2 0 0 1 2-2h19a2 2 0 0 1 2 2v28a2 2 0 0 1-2 2H27a2 2 0 0 1-2-2V10Z"
      })
    }),
    'group-stack': /*#__PURE__*/_jsx(SVG, {
      xmlns: "http://www.w3.org/2000/svg",
      width: "48",
      height: "48",
      viewBox: "0 0 48 48",
      children: /*#__PURE__*/_jsx(Path, {
        d: "M0 10a2 2 0 0 1 2-2h44a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V10Zm0 17a2 2 0 0 1 2-2h44a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V27Z"
      })
    }),
    'group-grid': /*#__PURE__*/_jsx(SVG, {
      xmlns: "http://www.w3.org/2000/svg",
      width: "48",
      height: "48",
      viewBox: "0 0 48 48",
      children: /*#__PURE__*/_jsx(Path, {
        d: "M0 10a2 2 0 0 1 2-2h19a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V10Zm25 0a2 2 0 0 1 2-2h19a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H27a2 2 0 0 1-2-2V10ZM0 27a2 2 0 0 1 2-2h19a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V27Zm25 0a2 2 0 0 1 2-2h19a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H27a2 2 0 0 1-2-2V27Z"
      })
    })
  };
  return icons?.[name];
};

/**
 * A custom hook to tell the Group block whether to show the variation placeholder.
 *
 * @param {Object}  props                  Arguments to pass to hook.
 * @param {Object}  [props.attributes]     The block's attributes.
 * @param {string}  [props.usedLayoutType] The block's current layout type.
 * @param {boolean} [props.hasInnerBlocks] Whether the block has inner blocks.
 *
 * @return {[boolean, Function]} A state value and setter function.
 */
export function useShouldShowPlaceHolder({
  attributes = {
    style: undefined,
    backgroundColor: undefined,
    textColor: undefined,
    fontSize: undefined
  },
  usedLayoutType = '',
  hasInnerBlocks = false
}) {
  const {
    style,
    backgroundColor,
    textColor,
    fontSize
  } = attributes;
  /*
   * Shows the placeholder when no known styles are set,
   * or when a non-default layout has been selected.
   * Should the Group block support more style presets in the
   * future, e.g., attributes.spacingSize, we can add them to the
   * condition.
   */
  const [showPlaceholder, setShowPlaceholder] = useState(!hasInnerBlocks && !backgroundColor && !fontSize && !textColor && !style && usedLayoutType !== 'flex' && usedLayoutType !== 'grid');
  useEffect(() => {
    if (!!hasInnerBlocks || !!backgroundColor || !!fontSize || !!textColor || !!style || usedLayoutType === 'flex') {
      setShowPlaceholder(false);
    }
  }, [backgroundColor, fontSize, textColor, style, usedLayoutType, hasInnerBlocks]);
  return [showPlaceholder, setShowPlaceholder];
}

/**
 * Display group variations if none is selected.
 *
 * @param {Object}   props          Component props.
 * @param {string}   props.name     The block's name.
 * @param {Function} props.onSelect Function to set block's attributes.
 *
 * @return {JSX.Element}                The placeholder.
 */
function GroupPlaceHolder({
  name,
  onSelect
}) {
  const variations = useSelect(select => select(blocksStore).getBlockVariations(name, 'block'), [name]);
  const blockProps = useBlockProps({
    className: 'wp-block-group__placeholder'
  });
  useEffect(() => {
    if (variations && variations.length === 1) {
      onSelect(variations[0]);
    }
  }, [onSelect, variations]);
  return /*#__PURE__*/_jsx("div", {
    ...blockProps,
    children: /*#__PURE__*/_jsx(Placeholder, {
      instructions: __('Group blocks together. Select a layout:'),
      children: /*#__PURE__*/_jsx("ul", {
        role: "list",
        className: "wp-block-group-placeholder__variations",
        "aria-label": __('Block variations'),
        children: variations.map(variation => /*#__PURE__*/_jsx("li", {
          children: /*#__PURE__*/_jsx(Button, {
            __next40pxDefaultSize: true,
            variant: "tertiary",
            icon: getGroupPlaceholderIcons(variation.name),
            iconSize: 48,
            onClick: () => onSelect(variation),
            className: "wp-block-group-placeholder__variation-button",
            label: `${variation.title}: ${variation.description}`
          })
        }, variation.name))
      })
    })
  });
}
export default GroupPlaceHolder;
//# sourceMappingURL=placeholder.js.map