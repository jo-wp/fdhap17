/**
 * WordPress dependencies
 */
import { createContext, useContext } from '@wordpress/element';
import { createHigherOrderComponent } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { jsx as _jsx } from "react/jsx-runtime";
const Context = createContext({
  name: null,
  icon: null
});
export const PluginContextProvider = Context.Provider;

/**
 * A hook that returns the plugin context.
 *
 * @return {PluginContext} Plugin context
 */
export function usePluginContext() {
  return useContext(Context);
}

/**
 * A Higher Order Component used to inject Plugin context to the
 * wrapped component.
 *
 * @param  mapContextToProps Function called on every context change,
 *                           expected to return object of props to
 *                           merge with the component's own props.
 *
 * @return {Component} Enhanced component with injected context as props.
 */
export const withPluginContext = mapContextToProps => createHigherOrderComponent(OriginalComponent => {
  return props => /*#__PURE__*/_jsx(Context.Consumer, {
    children: context => /*#__PURE__*/_jsx(OriginalComponent, {
      ...props,
      ...mapContextToProps(context, props)
    })
  });
}, 'withPluginContext');
//# sourceMappingURL=index.js.map