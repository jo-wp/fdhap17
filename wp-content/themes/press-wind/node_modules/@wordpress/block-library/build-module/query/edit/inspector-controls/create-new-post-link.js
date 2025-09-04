/**
 * WordPress dependencies
 */
import { createInterpolateElement } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { jsx as _jsx } from "react/jsx-runtime";
const CreateNewPostLink = ({
  postType
}) => {
  const newPostUrl = addQueryArgs('post-new.php', {
    post_type: postType
  });
  const addNewItemLabel = useSelect(select => {
    const {
      getPostType
    } = select(coreStore);
    return getPostType(postType)?.labels?.add_new_item;
  }, [postType]);
  return /*#__PURE__*/_jsx("div", {
    className: "wp-block-query__create-new-link",
    children: createInterpolateElement('<a>' + addNewItemLabel + '</a>',
    // eslint-disable-next-line jsx-a11y/anchor-has-content
    {
      a: /*#__PURE__*/_jsx("a", {
        href: newPostUrl
      })
    })
  });
};
export default CreateNewPostLink;
//# sourceMappingURL=create-new-post-link.js.map