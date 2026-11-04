import '../styles/main.css';
import { initNavigation } from './navigation';

initNavigation();

// Optional modules are code-split: nothing below is downloaded unless the page uses it.
if (document.querySelector('[data-animate]')) {
	void import('./animations/reveal').then(({ initReveal }) => initReveal());
}
