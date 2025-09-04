/**
 * Internal dependencies
 */
import type { AdditionalData, OnChangeHandler, OnErrorHandler, OnSuccessHandler } from './types';
interface UploadMediaArgs {
    additionalData?: AdditionalData;
    allowedTypes?: string[];
    filesList: File[];
    maxUploadFileSize?: number;
    onError?: OnErrorHandler;
    onFileChange?: OnChangeHandler;
    onSuccess?: OnSuccessHandler;
    wpAllowedMimeTypes?: Record<string, string> | null;
    signal?: AbortSignal;
}
/**
 * Upload a media file when the file upload button is activated
 * or when adding a file to the editor via drag & drop.
 *
 * @param $0                    Parameters object passed to the function.
 * @param $0.allowedTypes       Array with the types of media that can be uploaded, if unset all types are allowed.
 * @param $0.additionalData     Additional data to include in the request.
 * @param $0.filesList          List of files.
 * @param $0.maxUploadFileSize  Maximum upload size in bytes allowed for the site.
 * @param $0.onError            Function called when an error happens.
 * @param $0.onFileChange       Function called each time a file or a temporary representation of the file is available.
 * @param $0.wpAllowedMimeTypes List of allowed mime types and file extensions.
 * @param $0.signal             Abort signal.
 */
export declare function uploadMedia({ wpAllowedMimeTypes, allowedTypes, additionalData, filesList, maxUploadFileSize, onError, onFileChange, signal, }: UploadMediaArgs): void;
export {};
//# sourceMappingURL=upload-media.d.ts.map