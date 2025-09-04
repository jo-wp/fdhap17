/**
 * Internal dependencies
 */

/**
 * Transforms an attachment object from the REST API shape into the shape expected by the block editor and other consumers.
 *
 * @param attachment REST API attachment object.
 */
export function transformAttachment(attachment) {
  var _attachment$caption$r;
  // eslint-disable-next-line camelcase
  const {
    alt_text,
    source_url,
    ...savedMediaProps
  } = attachment;
  return {
    ...savedMediaProps,
    alt: attachment.alt_text,
    caption: (_attachment$caption$r = attachment.caption?.raw) !== null && _attachment$caption$r !== void 0 ? _attachment$caption$r : '',
    title: attachment.title.raw,
    url: attachment.source_url,
    poster: attachment._embedded?.['wp:featuredmedia']?.[0]?.source_url || undefined
  };
}
//# sourceMappingURL=transform-attachment.js.map