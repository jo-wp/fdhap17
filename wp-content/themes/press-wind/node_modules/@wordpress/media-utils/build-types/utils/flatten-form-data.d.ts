/**
 * Recursively flatten data passed to form data, to allow using multi-level objects.
 *
 * @param {FormData}      formData Form data object.
 * @param {string}        key      Key to amend to form data object
 * @param {string|Object} data     Data to be amended to form data.
 */
export declare function flattenFormData(formData: FormData, key: string, data: string | undefined | Record<string, string>): void;
//# sourceMappingURL=flatten-form-data.d.ts.map