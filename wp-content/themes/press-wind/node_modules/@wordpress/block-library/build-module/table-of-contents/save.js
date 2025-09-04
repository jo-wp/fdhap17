/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import TableOfContentsList from './list';
import { linearToNestedHeadingList } from './utils';
import { jsx as _jsx } from "react/jsx-runtime";
export default function save({
  attributes: {
    headings = []
  }
}) {
  if (headings.length === 0) {
    return null;
  }
  return /*#__PURE__*/_jsx("nav", {
    ...useBlockProps.save(),
    children: /*#__PURE__*/_jsx("ol", {
      children: /*#__PURE__*/_jsx(TableOfContentsList, {
        nestedHeadingList: linearToNestedHeadingList(headings)
      })
    })
  });
}
//# sourceMappingURL=save.js.map