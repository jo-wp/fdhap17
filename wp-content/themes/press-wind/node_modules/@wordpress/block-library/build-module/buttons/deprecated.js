/**
 * External dependencies
 */
import clsx from 'clsx';
/**
 * WordPress dependencies
 */
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

/**
 * @param {Object} attributes Block's attributes.
 */
import { jsx as _jsx } from "react/jsx-runtime";
const migrateWithLayout = attributes => {
  if (!!attributes.layout) {
    return attributes;
  }
  const {
    contentJustification,
    orientation,
    ...updatedAttributes
  } = attributes;
  if (contentJustification || orientation) {
    Object.assign(updatedAttributes, {
      layout: {
        type: 'flex',
        ...(contentJustification && {
          justifyContent: contentJustification
        }),
        ...(orientation && {
          orientation
        })
      }
    });
  }
  return updatedAttributes;
};
const deprecated = [{
  attributes: {
    contentJustification: {
      type: 'string'
    },
    orientation: {
      type: 'string',
      default: 'horizontal'
    }
  },
  supports: {
    anchor: true,
    align: ['wide', 'full'],
    __experimentalExposeControlsToChildren: true,
    spacing: {
      blockGap: true,
      margin: ['top', 'bottom'],
      __experimentalDefaultControls: {
        blockGap: true
      }
    }
  },
  isEligible: ({
    contentJustification,
    orientation
  }) => !!contentJustification || !!orientation,
  migrate: migrateWithLayout,
  save({
    attributes: {
      contentJustification,
      orientation
    }
  }) {
    return /*#__PURE__*/_jsx("div", {
      ...useBlockProps.save({
        className: clsx({
          [`is-content-justification-${contentJustification}`]: contentJustification,
          'is-vertical': orientation === 'vertical'
        })
      }),
      children: /*#__PURE__*/_jsx(InnerBlocks.Content, {})
    });
  }
}, {
  supports: {
    align: ['center', 'left', 'right'],
    anchor: true
  },
  save() {
    return /*#__PURE__*/_jsx("div", {
      children: /*#__PURE__*/_jsx(InnerBlocks.Content, {})
    });
  },
  isEligible({
    align
  }) {
    return align && ['center', 'left', 'right'].includes(align);
  },
  migrate(attributes) {
    return migrateWithLayout({
      ...attributes,
      align: undefined,
      // Floating Buttons blocks shouldn't have been supported in the
      // first place. Most users using them probably expected them to
      // act like content justification controls, so these blocks are
      // migrated to use content justification.
      // As for center-aligned Buttons blocks, the content justification
      // equivalent will create an identical end result in most cases.
      contentJustification: attributes.align
    });
  }
}];
export default deprecated;
//# sourceMappingURL=deprecated.js.map