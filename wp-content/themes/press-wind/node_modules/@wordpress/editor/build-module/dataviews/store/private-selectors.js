/**
 * Internal dependencies
 */

const EMPTY_ARRAY = [];
export function getEntityActions(state, kind, name) {
  var _state$actions$kind$n;
  return (_state$actions$kind$n = state.actions[kind]?.[name]) !== null && _state$actions$kind$n !== void 0 ? _state$actions$kind$n : EMPTY_ARRAY;
}
export function isEntityReady(state, kind, name) {
  return state.isReady[kind]?.[name];
}
//# sourceMappingURL=private-selectors.js.map