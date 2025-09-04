import type { Post, TemplatePart, Template } from '../types';
export declare function isTemplate(post: Post): post is Template;
export declare function isTemplatePart(post: Post): post is TemplatePart;
export declare function isTemplateOrTemplatePart(p: Post): p is Template | TemplatePart;
export declare function getItemTitle(item: Post): string;
/**
 * Check if a template is removable.
 *
 * @param template The template entity to check.
 * @return Whether the template is removable.
 */
export declare function isTemplateRemovable(template: Template | TemplatePart): boolean;
//# sourceMappingURL=utils.d.ts.map