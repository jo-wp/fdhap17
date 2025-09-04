/**
 * WordPress dependencies
 */
import { PanelBody } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { store as editorStore } from '../../store';
import PostTaxonomiesForm from './index';
import PostTaxonomiesCheck from './check';
import { jsx as _jsx } from "react/jsx-runtime";
function TaxonomyPanel({
  taxonomy,
  children
}) {
  const slug = taxonomy?.slug;
  const panelName = slug ? `taxonomy-panel-${slug}` : '';
  const {
    isEnabled,
    isOpened
  } = useSelect(select => {
    const {
      isEditorPanelEnabled,
      isEditorPanelOpened
    } = select(editorStore);
    return {
      isEnabled: slug ? isEditorPanelEnabled(panelName) : false,
      isOpened: slug ? isEditorPanelOpened(panelName) : false
    };
  }, [panelName, slug]);
  const {
    toggleEditorPanelOpened
  } = useDispatch(editorStore);
  if (!isEnabled) {
    return null;
  }
  const taxonomyMenuName = taxonomy?.labels?.menu_name;
  if (!taxonomyMenuName) {
    return null;
  }
  return /*#__PURE__*/_jsx(PanelBody, {
    title: taxonomyMenuName,
    opened: isOpened,
    onToggle: () => toggleEditorPanelOpened(panelName),
    children: children
  });
}
function PostTaxonomies() {
  return /*#__PURE__*/_jsx(PostTaxonomiesCheck, {
    children: /*#__PURE__*/_jsx(PostTaxonomiesForm, {
      taxonomyWrapper: (content, taxonomy) => {
        return /*#__PURE__*/_jsx(TaxonomyPanel, {
          taxonomy: taxonomy,
          children: content
        });
      }
    })
  });
}

/**
 * Renders a panel for a specific taxonomy.
 *
 * @param {Object}  props          The component props.
 * @param {Object}  props.taxonomy The taxonomy object.
 * @param {Element} props.children The child components.
 *
 * @return {Component} The rendered taxonomy panel.
 */
export default PostTaxonomies;
//# sourceMappingURL=panel.js.map