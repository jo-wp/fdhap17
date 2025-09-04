/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { BlockSettingsMenuControls, store as blockEditorStore } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import ConvertToRegularBlocks from './convert-to-regular';
import ConvertToTemplatePart from './convert-to-template-part';
import { jsx as _jsx } from "react/jsx-runtime";
export default function TemplatePartMenuItems() {
  return /*#__PURE__*/_jsx(BlockSettingsMenuControls, {
    children: ({
      selectedClientIds,
      onClose
    }) => /*#__PURE__*/_jsx(TemplatePartConverterMenuItem, {
      clientIds: selectedClientIds,
      onClose: onClose
    })
  });
}
function TemplatePartConverterMenuItem({
  clientIds,
  onClose
}) {
  const {
    isContentOnly,
    blocks
  } = useSelect(select => {
    const {
      getBlocksByClientId,
      getBlockEditingMode
    } = select(blockEditorStore);
    return {
      blocks: getBlocksByClientId(clientIds),
      isContentOnly: clientIds.length === 1 && getBlockEditingMode(clientIds[0]) === 'contentOnly'
    };
  }, [clientIds]);

  // Do not show the convert button if the block is in content-only mode.
  if (isContentOnly) {
    return null;
  }

  // Allow converting a single template part to standard blocks.
  if (blocks.length === 1 && blocks[0]?.name === 'core/template-part') {
    return /*#__PURE__*/_jsx(ConvertToRegularBlocks, {
      clientId: clientIds[0],
      onClose: onClose
    });
  }
  return /*#__PURE__*/_jsx(ConvertToTemplatePart, {
    clientIds: clientIds,
    blocks: blocks
  });
}
//# sourceMappingURL=index.js.map