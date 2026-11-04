import { Component, StrictMode, type ErrorInfo, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { ExampleCounter, parseConfig } from './ExampleCounter';

class ErrorBoundary extends Component<{ children: ReactNode }, { failed: boolean }> {
	state = { failed: false };

	static getDerivedStateFromError(): { failed: boolean } {
		return { failed: true };
	}

	componentDidCatch(error: Error, info: ErrorInfo): void {
		console.error('React component failed', error, info.componentStack);
	}

	render(): ReactNode {
		return this.state.failed ? <p role="alert">This component could not be loaded.</p> : this.props.children;
	}
}

const roots: Root[] = [];

/**
 * Mounts each `[data-react-root="example"]` element. Server-provided configuration is
 * read from the `data-config` JSON attribute, which PHP outputs with wp_json_encode()
 * and esc_attr(). The server-rendered children act as a no-JS fallback.
 */
function mount(): void {
	document.querySelectorAll<HTMLElement>('[data-react-root="example"]').forEach((element) => {
		const root = createRoot(element);
		roots.push(root);
		root.render(
			<StrictMode>
				<ErrorBoundary>
					<ExampleCounter config={parseConfig(element.dataset.config)} />
				</ErrorBoundary>
			</StrictMode>
		);
	});
}

window.addEventListener('pagehide', () => roots.splice(0).forEach((root) => root.unmount()), { once: true });

mount();
