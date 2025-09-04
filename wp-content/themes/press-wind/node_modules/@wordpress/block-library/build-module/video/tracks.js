import { jsx as _jsx } from "react/jsx-runtime";
export default function Tracks({
  tracks = []
}) {
  return tracks.map(track => {
    return /*#__PURE__*/_jsx("track", {
      ...track
    }, track.src);
  });
}
//# sourceMappingURL=tracks.js.map