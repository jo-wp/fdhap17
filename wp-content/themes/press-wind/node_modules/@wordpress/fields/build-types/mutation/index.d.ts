/**
 * Internal dependencies
 */
import type { Post } from '../types';
export type NoticeSettings<T extends Post> = {
    success: {
        id?: string;
        type?: string;
        messages: {
            getMessage: (posts: T) => string;
            getBatchMessage: (posts: T[]) => string;
        };
    };
    error: {
        id?: string;
        type?: string;
        messages: {
            getMessage: (errors: Set<string>) => string;
            getBatchMessage: (errors: Set<string>) => string;
        };
    };
};
export declare const deletePostWithNotices: <T extends Post>(posts: T[], notice: NoticeSettings<T>, callbacks: {
    onActionPerformed?: (posts: T[]) => void;
    onActionError?: () => void;
}) => Promise<void>;
export declare const editPostWithNotices: <T extends Post>(postsWithUpdates: {
    originalPost: T;
    changes: Partial<T>;
}[], notice: NoticeSettings<T>, callbacks: {
    onActionPerformed?: (posts: T[]) => void;
    onActionError?: () => void;
}) => Promise<void>;
//# sourceMappingURL=index.d.ts.map