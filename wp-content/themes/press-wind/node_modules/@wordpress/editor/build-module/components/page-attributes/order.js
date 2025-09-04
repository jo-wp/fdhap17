/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Flex, FlexBlock, __experimentalNumberControl as NumberControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import PostTypeSupportCheck from '../post-type-support-check';
import { store as editorStore } from '../../store';
import { jsx as _jsx } from "react/jsx-runtime";
function PageAttributesOrder() {
  const order = useSelect(select => {
    var _select$getEditedPost;
    return (_select$getEditedPost = select(editorStore).getEditedPostAttribute('menu_order')) !== null && _select$getEditedPost !== void 0 ? _select$getEditedPost : 0;
  }, []);
  const {
    editPost
  } = useDispatch(editorStore);
  const [orderInput, setOrderInput] = useState(null);
  const setUpdatedOrder = value => {
    setOrderInput(value);
    const newOrder = Number(value);
    if (Number.isInteger(newOrder) && value.trim?.() !== '') {
      editPost({
        menu_order: newOrder
      });
    }
  };
  const value = orderInput !== null && orderInput !== void 0 ? orderInput : order;
  return /*#__PURE__*/_jsx(Flex, {
    children: /*#__PURE__*/_jsx(FlexBlock, {
      children: /*#__PURE__*/_jsx(NumberControl, {
        __next40pxDefaultSize: true,
        label: __('Order'),
        help: __('Set the page order.'),
        value: value,
        onChange: setUpdatedOrder,
        hideLabelFromVision: true,
        onBlur: () => {
          setOrderInput(null);
        }
      })
    })
  });
}

/**
 * Renders the Page Attributes Order component. A number input in an editor interface
 * for setting the order of a given page.
 * The component is now not used in core but was kept for backward compatibility.
 *
 * @return {Component} The component to be rendered.
 */
export default function PageAttributesOrderWithChecks() {
  return /*#__PURE__*/_jsx(PostTypeSupportCheck, {
    supportKeys: "page-attributes",
    children: /*#__PURE__*/_jsx(PageAttributesOrder, {})
  });
}
//# sourceMappingURL=order.js.map