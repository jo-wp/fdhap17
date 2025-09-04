/**
 * Renders the component for managing saved states of entities.
 *
 * @param {Object}   props              The component props.
 * @param {Function} props.close        The function to close the dialog.
 * @param {Function} props.renderDialog The function to render the dialog.
 *
 * @return {JSX.Element} The rendered component.
 */
export default function EntitiesSavedStates({ close, renderDialog, }: {
    close: Function;
    renderDialog: Function;
}): JSX.Element;
/**
 * Renders a panel for saving entities with dirty records.
 *
 * @param {Object}   props                       The component props.
 * @param {string}   props.additionalPrompt      Additional prompt to display.
 * @param {Function} props.close                 Function to close the panel.
 * @param {Function} props.onSave                Function to call when saving entities.
 * @param {boolean}  props.saveEnabled           Flag indicating if save is enabled.
 * @param {string}   props.saveLabel             Label for the save button.
 * @param {Function} props.renderDialog          Function to render a custom dialog.
 * @param {Array}    props.dirtyEntityRecords    Array of dirty entity records.
 * @param {boolean}  props.isDirty               Flag indicating if there are dirty entities.
 * @param {Function} props.setUnselectedEntities Function to set unselected entities.
 * @param {Array}    props.unselectedEntities    Array of unselected entities.
 *
 * @return {JSX.Element} The rendered component.
 */
export function EntitiesSavedStatesExtensible({ additionalPrompt, close, onSave, saveEnabled: saveEnabledProp, saveLabel, renderDialog, dirtyEntityRecords, isDirty, setUnselectedEntities, unselectedEntities, }: {
    additionalPrompt: string;
    close: Function;
    onSave: Function;
    saveEnabled: boolean;
    saveLabel: string;
    renderDialog: Function;
    dirtyEntityRecords: any[];
    isDirty: boolean;
    setUnselectedEntities: Function;
    unselectedEntities: any[];
}): JSX.Element;
//# sourceMappingURL=index.d.ts.map