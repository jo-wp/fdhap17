/**
 * WordPress dependencies
 */
import { SVG, Rect } from '@wordpress/components';
import { jsx as _jsx } from "react/jsx-runtime";
export const circle = (size, color) => /*#__PURE__*/_jsx(SVG, {
  fill: "none",
  xmlns: "http://www.w3.org/2000/svg",
  children: /*#__PURE__*/_jsx(Rect, {
    width: size,
    height: size,
    rx: size / 2,
    fill: color
  })
});
export const circleOutline = (size, color) => /*#__PURE__*/_jsx(SVG, {
  width: size,
  height: size,
  fill: "none",
  xmlns: "http://www.w3.org/2000/svg",
  children: /*#__PURE__*/_jsx(Rect, {
    x: "0.5",
    y: "0.5",
    width: size - 1,
    height: size - 1,
    rx: size / 2,
    stroke: color
  })
});
export const square = (size, color) => /*#__PURE__*/_jsx(SVG, {
  fill: "none",
  xmlns: "http://www.w3.org/2000/svg",
  children: /*#__PURE__*/_jsx(Rect, {
    width: size,
    height: size,
    fill: color
  })
});
//# sourceMappingURL=icons.native.js.map