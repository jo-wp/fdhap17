/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';
/**
 * Fetches link suggestions from the WordPress API.
 *
 * WordPress does not support searching multiple tables at once, e.g. posts and terms, so we
 * perform multiple queries at the same time and then merge the results together.
 *
 * @param search
 * @param searchOptions
 * @param editorSettings
 *
 * @example
 * ```js
 * import { __experimentalFetchLinkSuggestions as fetchLinkSuggestions } from '@wordpress/core-data';
 *
 * //...
 *
 * export function initialize( id, settings ) {
 *
 * settings.__experimentalFetchLinkSuggestions = (
 *     search,
 *     searchOptions
 * ) => fetchLinkSuggestions( search, searchOptions, settings );
 * ```
 */
export default async function fetchLinkSuggestions(search, searchOptions = {}, editorSettings = {}) {
  const searchOptionsToUse = searchOptions.isInitialSuggestions && searchOptions.initialSuggestionsSearchOptions ? {
    ...searchOptions,
    ...searchOptions.initialSuggestionsSearchOptions
  } : searchOptions;
  const {
    type,
    subtype,
    page,
    perPage = searchOptions.isInitialSuggestions ? 3 : 20
  } = searchOptionsToUse;
  const {
    disablePostFormats = false
  } = editorSettings;
  const queries = [];
  if (!type || type === 'post') {
    queries.push(apiFetch({
      path: addQueryArgs('/wp/v2/search', {
        search,
        page,
        per_page: perPage,
        type: 'post',
        subtype
      })
    }).then(results => {
      return results.map(result => {
        return {
          id: result.id,
          url: result.url,
          title: decodeEntities(result.title || '') || __('(no title)'),
          type: result.subtype || result.type,
          kind: 'post-type'
        };
      });
    }).catch(() => []) // Fail by returning no results.
    );
  }
  if (!type || type === 'term') {
    queries.push(apiFetch({
      path: addQueryArgs('/wp/v2/search', {
        search,
        page,
        per_page: perPage,
        type: 'term',
        subtype
      })
    }).then(results => {
      return results.map(result => {
        return {
          id: result.id,
          url: result.url,
          title: decodeEntities(result.title || '') || __('(no title)'),
          type: result.subtype || result.type,
          kind: 'taxonomy'
        };
      });
    }).catch(() => []) // Fail by returning no results.
    );
  }
  if (!disablePostFormats && (!type || type === 'post-format')) {
    queries.push(apiFetch({
      path: addQueryArgs('/wp/v2/search', {
        search,
        page,
        per_page: perPage,
        type: 'post-format',
        subtype
      })
    }).then(results => {
      return results.map(result => {
        return {
          id: result.id,
          url: result.url,
          title: decodeEntities(result.title || '') || __('(no title)'),
          type: result.subtype || result.type,
          kind: 'taxonomy'
        };
      });
    }).catch(() => []) // Fail by returning no results.
    );
  }
  if (!type || type === 'attachment') {
    queries.push(apiFetch({
      path: addQueryArgs('/wp/v2/media', {
        search,
        page,
        per_page: perPage
      })
    }).then(results => {
      return results.map(result => {
        return {
          id: result.id,
          url: result.source_url,
          title: decodeEntities(result.title.rendered || '') || __('(no title)'),
          type: result.type,
          kind: 'media'
        };
      });
    }).catch(() => []) // Fail by returning no results.
    );
  }
  const responses = await Promise.all(queries);
  let results = responses.flat();
  results = results.filter(result => !!result.id);
  results = sortResults(results, search);
  results = results.slice(0, perPage);
  return results;
}

/**
 * Sort search results by relevance to the given query.
 *
 * Sorting is necessary as we're querying multiple endpoints and merging the results. For example
 * a taxonomy title might be more relevant than a post title, but by default taxonomy results will
 * be ordered after all the (potentially irrelevant) post results.
 *
 * We sort by scoring each result, where the score is the number of tokens in the title that are
 * also in the search query, divided by the total number of tokens in the title. This gives us a
 * score between 0 and 1, where 1 is a perfect match.
 *
 * @param results
 * @param search
 */
export function sortResults(results, search) {
  const searchTokens = tokenize(search);
  const scores = {};
  for (const result of results) {
    if (result.title) {
      const titleTokens = tokenize(result.title);
      const matchingTokens = titleTokens.filter(titleToken => searchTokens.some(searchToken => titleToken.includes(searchToken)));
      scores[result.id] = matchingTokens.length / titleTokens.length;
    } else {
      scores[result.id] = 0;
    }
  }
  return results.sort((a, b) => scores[b.id] - scores[a.id]);
}

/**
 * Turns text into an array of tokens, with whitespace and punctuation removed.
 *
 * For example, `"I'm having a ball."` becomes `[ "im", "having", "a", "ball" ]`.
 *
 * @param text
 */
export function tokenize(text) {
  // \p{L} matches any kind of letter from any language.
  // \p{N} matches any kind of numeric character.
  return text.toLowerCase().match(/[\p{L}\p{N}]+/gu) || [];
}
//# sourceMappingURL=__experimental-fetch-link-suggestions.js.map