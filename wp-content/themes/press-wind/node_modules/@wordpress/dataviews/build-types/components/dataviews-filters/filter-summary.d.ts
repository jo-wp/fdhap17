import type { RefObject } from 'react';
import type { NormalizedFilter, View } from '../../types';
interface OperatorSelectorProps {
    filter: NormalizedFilter;
    view: View;
    onChangeView: (view: View) => void;
}
interface FilterSummaryProps extends OperatorSelectorProps {
    addFilterRef: RefObject<HTMLButtonElement>;
    openedFilter: string | null;
}
export default function FilterSummary({ addFilterRef, openedFilter, ...commonProps }: FilterSummaryProps): import("react").JSX.Element;
export {};
//# sourceMappingURL=filter-summary.d.ts.map