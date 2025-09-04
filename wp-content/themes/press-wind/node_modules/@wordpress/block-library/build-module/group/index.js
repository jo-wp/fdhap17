/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { group as icon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import initBlock from '../utils/init-block';
import deprecated from './deprecated';
import edit from './edit';
const metadata = {
  $schema: "https://schemas.wp.org/trunk/block.json",
  apiVersion: 3,
  name: "core/group",
  title: "Group",
  category: "design",
  description: "Gather blocks in a layout container.",
  keywords: ["container", "wrapper", "row", "section"],
  textdomain: "default",
  attributes: {
    tagName: {
      type: "string",
      "default": "div"
    },
    templateLock: {
      type: ["string", "boolean"],
      "enum": ["all", "insert", "contentOnly", false]
    },
    allowedBlocks: {
      type: "array"
    }
  },
  supports: {
    __experimentalOnEnter: true,
    __experimentalOnMerge: true,
    __experimentalSettings: true,
    align: ["wide", "full"],
    anchor: true,
    ariaLabel: true,
    html: false,
    background: {
      backgroundImage: true,
      backgroundSize: true,
      __experimentalDefaultControls: {
        backgroundImage: true
      }
    },
    color: {
      gradients: true,
      heading: true,
      button: true,
      link: true,
      __experimentalDefaultControls: {
        background: true,
        text: true
      }
    },
    shadow: true,
    spacing: {
      margin: ["top", "bottom"],
      padding: true,
      blockGap: true,
      __experimentalDefaultControls: {
        padding: true,
        blockGap: true
      }
    },
    dimensions: {
      minHeight: true
    },
    __experimentalBorder: {
      color: true,
      radius: true,
      style: true,
      width: true,
      __experimentalDefaultControls: {
        color: true,
        radius: true,
        style: true,
        width: true
      }
    },
    position: {
      sticky: true
    },
    typography: {
      fontSize: true,
      lineHeight: true,
      __experimentalFontFamily: true,
      __experimentalFontWeight: true,
      __experimentalFontStyle: true,
      __experimentalTextTransform: true,
      __experimentalTextDecoration: true,
      __experimentalLetterSpacing: true,
      __experimentalDefaultControls: {
        fontSize: true
      }
    },
    layout: {
      allowSizingOnChildren: true
    },
    interactivity: {
      clientNavigation: true
    }
  },
  editorStyle: "wp-block-group-editor",
  style: "wp-block-group"
};
import save from './save';
import transforms from './transforms';
import variations from './variations';
const {
  name
} = metadata;
export { metadata, name };
export const settings = {
  icon,
  example: {
    attributes: {
      layout: {
        type: 'constrained',
        justifyContent: 'center'
      },
      style: {
        spacing: {
          padding: {
            top: '4em',
            right: '3em',
            bottom: '4em',
            left: '3em'
          }
        }
      }
    },
    innerBlocks: [{
      name: 'core/heading',
      attributes: {
        content: __('La Mancha'),
        textAlign: 'center'
      }
    }, {
      name: 'core/paragraph',
      attributes: {
        align: 'center',
        content: __('In a village of La Mancha, the name of which I have no desire to call to mind, there lived not long since one of those gentlemen that keep a lance in the lance-rack, an old buckler, a lean hack, and a greyhound for coursing.')
      }
    }, {
      name: 'core/spacer',
      attributes: {
        height: '10px'
      }
    }, {
      name: 'core/button',
      attributes: {
        text: __('Read more')
      }
    }],
    viewportWidth: 600
  },
  transforms,
  edit,
  save,
  deprecated,
  variations
};
export const init = () => initBlock({
  name,
  metadata,
  settings
});
//# sourceMappingURL=index.js.map