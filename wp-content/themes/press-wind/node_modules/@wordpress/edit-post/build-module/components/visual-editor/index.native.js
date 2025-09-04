/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { BlockList } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import Header from './header';
import { jsx as _jsx } from "react/jsx-runtime";
export default class VisualEditor extends Component {
  constructor(props) {
    super(props);
    this.renderHeader = this.renderHeader.bind(this);
  }
  renderHeader() {
    const {
      setTitleRef
    } = this.props;
    return /*#__PURE__*/_jsx(Header, {
      setTitleRef: setTitleRef
    });
  }
  render() {
    const {
      safeAreaBottomInset
    } = this.props;
    return /*#__PURE__*/_jsx(BlockList, {
      header: this.renderHeader,
      safeAreaBottomInset: safeAreaBottomInset
    });
  }
}
//# sourceMappingURL=index.native.js.map