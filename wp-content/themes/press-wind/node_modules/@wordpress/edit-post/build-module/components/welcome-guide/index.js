/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import WelcomeGuideDefault from './default';
import WelcomeGuideTemplate from './template';
import { store as editPostStore } from '../../store';
import { jsx as _jsx } from "react/jsx-runtime";
export default function WelcomeGuide({
  postType
}) {
  const {
    isActive,
    isEditingTemplate
  } = useSelect(select => {
    const {
      isFeatureActive
    } = select(editPostStore);
    const _isEditingTemplate = postType === 'wp_template';
    const feature = _isEditingTemplate ? 'welcomeGuideTemplate' : 'welcomeGuide';
    return {
      isActive: isFeatureActive(feature),
      isEditingTemplate: _isEditingTemplate
    };
  }, [postType]);
  if (!isActive) {
    return null;
  }
  return isEditingTemplate ? /*#__PURE__*/_jsx(WelcomeGuideTemplate, {}) : /*#__PURE__*/_jsx(WelcomeGuideDefault, {});
}
//# sourceMappingURL=index.js.map