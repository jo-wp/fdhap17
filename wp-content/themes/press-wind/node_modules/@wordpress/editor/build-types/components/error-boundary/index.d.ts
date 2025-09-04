export default ErrorBoundary;
declare class ErrorBoundary extends Component<any, any, any> {
    static getDerivedStateFromError(error: any): {
        error: any;
    };
    constructor(...args: any[]);
    state: {
        error: null;
    };
    componentDidCatch(error: any): void;
    render(): any;
}
import { Component } from '@wordpress/element';
//# sourceMappingURL=index.d.ts.map