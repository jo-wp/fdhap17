/**
 * Internal dependencies
 */

function sort(valueA, valueB, direction) {
  return direction === 'asc' ? valueA.localeCompare(valueB) : valueB.localeCompare(valueA);
}
function isValid(value, context) {
  if (context?.elements) {
    const validValues = context?.elements?.map(f => f.value);
    if (!validValues.includes(value)) {
      return false;
    }
  }
  return true;
}
export default {
  sort,
  isValid,
  Edit: 'text'
};
//# sourceMappingURL=text.js.map