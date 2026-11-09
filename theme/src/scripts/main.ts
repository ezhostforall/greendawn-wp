import '../styles/main.css';
import { initNavigation } from './navigation';
import { initStickyHeader } from './sticky-header';
import { initCF7Attribution } from './cf7-attribution';

initNavigation();
initStickyHeader();
initCF7Attribution();

// Optional modules are code-split: nothing below is downloaded unless the page uses it.
if (document.querySelector('[data-animate]')) {
	void import('./animations/reveal').then(({ initReveal }) => initReveal());
}
