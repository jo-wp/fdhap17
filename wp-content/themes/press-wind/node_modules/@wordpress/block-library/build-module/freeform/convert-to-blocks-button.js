/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { ToolbarButton } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { rawHandler, serialize } from '@wordpress/blocks';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { jsx as _jsx } from "react/jsx-runtime";
const ConvertToBlocksButton = ({
  clientId
}) => {
  const {
    replaceBlocks
  } = useDispatch(blockEditorStore);
  const block = useSelect(select => {
    return select(blockEditorStore).getBlock(clientId);
  }, [clientId]);
  return /*#__PURE__*/_jsx(ToolbarButton, {
    onClick: () => replaceBlocks(block.clientId, rawHandler({
      HTML: serialize(block)
    })),
    children: __('Convert to blocks')
  });
};
export default ConvertToBlocksButton;
//# sourceMappingURL=convert-to-blocks-button.js.map