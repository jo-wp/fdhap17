/**
 * External dependencies
 */
import { View } from 'react-native';
/**
 * WordPress dependencies
 */
import { InnerBlocks, store as blockEditorStore } from '@wordpress/block-editor';
import { withDispatch, withSelect } from '@wordpress/data';
import { useRef, useEffect, useState } from '@wordpress/element';
import { compose, usePreferredColorSchemeStyle } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import styles from './editor.scss';

// Template contains the links that show when start.
import { jsx as _jsx } from "react/jsx-runtime";
const TEMPLATE = [['core/social-link-wordpress', {
  service: 'wordpress',
  url: 'https://wordpress.org'
}], ['core/social-link-facebook', {
  service: 'facebook'
}], ['core/social-link-twitter', {
  service: 'twitter'
}], ['core/social-link-instagram', {
  service: 'instagram'
}]];
function SocialLinksEdit({
  shouldDelete,
  onDelete,
  isSelected,
  isInnerIconSelected,
  innerBlocks,
  attributes,
  activeInnerBlocks,
  getBlock,
  blockWidth
}) {
  const [initialCreation, setInitialCreation] = useState(true);
  const shouldRenderFooterAppender = isSelected || isInnerIconSelected;
  const {
    align
  } = attributes;
  const {
    marginLeft: spacing
  } = styles.spacing;
  useEffect(() => {
    if (!shouldRenderFooterAppender) {
      setInitialCreation(false);
    }
  }, [shouldRenderFooterAppender]);
  const renderFooterAppender = useRef(() => /*#__PURE__*/_jsx(View, {
    style: styles.footerAppenderContainer,
    children: /*#__PURE__*/_jsx(InnerBlocks.ButtonBlockAppender, {
      isFloating: true
    })
  }));
  const placeholderStyle = usePreferredColorSchemeStyle(styles.placeholder, styles.placeholderDark);
  function renderPlaceholder() {
    return [...new Array(innerBlocks.length || 1)].map((_, index) => /*#__PURE__*/_jsx(View, {
      testID: "social-links-placeholder",
      style: placeholderStyle
    }, index));
  }
  function filterInnerBlocks(blockIds) {
    return blockIds.filter(blockId => getBlock(blockId).attributes.url);
  }
  if (!shouldRenderFooterAppender && activeInnerBlocks.length === 0) {
    return /*#__PURE__*/_jsx(View, {
      style: styles.placeholderWrapper,
      children: renderPlaceholder()
    });
  }
  return /*#__PURE__*/_jsx(InnerBlocks, {
    templateLock: false,
    template: initialCreation && TEMPLATE,
    renderFooterAppender: shouldRenderFooterAppender && renderFooterAppender.current,
    orientation: "horizontal",
    onDeleteBlock: shouldDelete ? onDelete : undefined,
    marginVertical: spacing,
    marginHorizontal: spacing,
    horizontalAlignment: align,
    filterInnerBlocks: !shouldRenderFooterAppender && filterInnerBlocks,
    blockWidth: blockWidth
  });
}
export default compose(withSelect((select, {
  clientId
}) => {
  const {
    getBlockCount,
    getBlockParents,
    getSelectedBlockClientId,
    getBlocks,
    getBlock
  } = select(blockEditorStore);
  const selectedBlockClientId = getSelectedBlockClientId();
  const selectedBlockParents = getBlockParents(selectedBlockClientId, true);
  const innerBlocks = getBlocks(clientId);
  const activeInnerBlocks = innerBlocks.filter(block => block.attributes?.url);
  return {
    shouldDelete: getBlockCount(clientId) === 1,
    isInnerIconSelected: selectedBlockParents[0] === clientId,
    innerBlocks,
    activeInnerBlocks,
    getBlock
  };
}), withDispatch((dispatch, {
  clientId
}) => {
  const {
    removeBlock
  } = dispatch(blockEditorStore);
  return {
    onDelete: () => {
      removeBlock(clientId, false);
    }
  };
}))(SocialLinksEdit);
//# sourceMappingURL=edit.native.js.map