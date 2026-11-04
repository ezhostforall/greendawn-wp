import { useState } from 'react';
import './ExampleCounter.css';

export interface ExampleConfig {
	label: string;
	start: number;
}

const defaults: ExampleConfig = { label: 'Count', start: 0 };

/** Validates untrusted JSON from the DOM instead of casting it. */
export function parseConfig(raw: string | undefined): ExampleConfig {
	try {
		const data: unknown = JSON.parse(raw ?? '{}');
		if (typeof data === 'object' && data !== null) {
			const { label, start } = data as Record<string, unknown>;
			return {
				label: typeof label === 'string' ? label : defaults.label,
				start: typeof start === 'number' && Number.isFinite(start) ? start : defaults.start,
			};
		}
	} catch {
		// Fall through to defaults.
	}
	return defaults;
}

export function ExampleCounter({ config }: { config: ExampleConfig }) {
	const [count, setCount] = useState(config.start);
	return (
		<div className="example-counter">
			<p aria-live="polite">
				{config.label}: <strong>{count}</strong>
			</p>
			<button type="button" className="example-counter__button" onClick={() => setCount((value) => value + 1)}>
				Increment
			</button>
		</div>
	);
}
