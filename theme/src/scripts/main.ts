import '../styles/main.css';
import { initNavigation } from './navigation';
import { initStickyHeader } from './sticky-header';
import { initCF7Attribution } from './cf7-attribution';
import { initTrackingConsent } from './tracking-consent';
import { initCF7Tracking } from './tracking-forms';

initNavigation();
initStickyHeader();
initCF7Attribution();
initTrackingConsent();
initCF7Tracking();

// Optional modules are code-split: nothing below is downloaded unless the page uses it.
if (document.querySelector('[data-animate]')) {
	void import('./animations/reveal').then(({ initReveal }) => initReveal());
}
