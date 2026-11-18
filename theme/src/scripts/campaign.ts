import '../styles/campaign.css';
import { campaignIsActive, campaignPhase, type CampaignTimeline } from './campaign-phase';

const roots = Array.from(document.querySelectorAll<HTMLElement>('[data-gd-campaign-timeline]'), element => ({
	element,
	timeline: JSON.parse(element.dataset.gdCampaignTimeline ?? '{}') as CampaignTimeline,
}));

const popup = document.querySelector<HTMLDialogElement>('[data-gd-campaign-popup]');
const popupTimeline = roots.find(({ element }) => element === popup)?.timeline;
const barLink = document.querySelector<HTMLElement>('[data-gd-campaign-bar-link]');
const sessionKey = `gd-campaign-popup-${popup?.dataset.gdCampaignPopup ?? ''}`;
let seen = false;
let ready = false;
let phaseTimer = 0;
let popupTimer = 0;
let retryTimer = 0;
let opener: HTMLElement | null = null;
const startScroll = window.scrollY;

try {
	seen = sessionStorage.getItem(sessionKey) === '1';
} catch {
	// The in-memory flag still prevents a repeat on this page.
}

function maybeOpen(): void {
	if (!popup || !popupTimeline || typeof popup.showModal !== 'function' || seen || !ready
		|| document.hidden || !campaignIsActive(popupTimeline)) return;

	if (document.querySelector('.nav-disclosure[open], dialog[open], [data-lead-capture-open][aria-busy="true"], .cmplz-cookiebanner.cmplz-show:not(.cmplz-dismissed, .cmplz-hidden)')
		|| document.activeElement?.matches('input, textarea, select, [contenteditable="true"]')) {
		window.clearTimeout(retryTimer);
		retryTimer = window.setTimeout(maybeOpen, 300);
		return;
	}

	const focused = document.activeElement;
	opener = focused instanceof HTMLElement && focused !== document.body ? focused : barLink;
	popup.showModal();
	seen = true;
	window.clearTimeout(popupTimer);
	window.clearTimeout(retryTimer);
	window.removeEventListener('scroll', handleScroll);
	try {
		sessionStorage.setItem(sessionKey, '1');
	} catch {
		// The in-memory flag still prevents a repeat on this page.
	}
}

function handleScroll(): void {
	if (window.scrollY - startScroll >= 240) {
		ready = true;
		maybeOpen();
	}
}

function syncPhases(): void {
	window.clearTimeout(phaseTimer);
	const now = Date.now() / 1000;
	let next = Infinity;
	for (const { element, timeline } of roots) {
		const phase = campaignPhase(timeline, now);
		if (element.hasAttribute('data-gd-campaign-notice')) {
			element.hidden = phase !== 'live' && phase !== 'late';
		}
		if (element === popup && popup.open && (phase === 'closed' || phase === 'upcoming')) popup.close();
		element.querySelectorAll<HTMLElement>('[data-gd-campaign-variant]').forEach(node => {
			node.hidden = !(node.dataset.gdCampaignVariant ?? '').split(' ').includes(phase);
		});
		for (const time of [timeline.startsAt, timeline.lateAt, timeline.endsAt]) {
			if (time && now < time) next = Math.min(next, time);
		}
	}
	document.dispatchEvent(new Event('greendawn:campaign-phase'));
	if (Number.isFinite(next)) phaseTimer = window.setTimeout(syncPhases, Math.min(2_147_000_000, (next - now) * 1000 + 10));
	maybeOpen();
}

if (popup && !seen) {
	window.addEventListener('scroll', handleScroll, { passive: true });
	popupTimer = window.setTimeout(() => {
		ready = true;
		maybeOpen();
	}, 6000);
}

popup?.addEventListener('close', () => {
	opener?.focus({ preventScroll: true });
	opener = null;
});

popup?.addEventListener('click', event => {
	if (!(event.target instanceof Element)) return;
	const action = event.target.closest<HTMLAnchorElement>('a');
	if (action && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey) {
		popup.close();
	}
});

document.addEventListener('visibilitychange', () => {
	if (!document.hidden) syncPhases();
});
window.addEventListener('pageshow', syncPhases);
window.addEventListener('pagehide', event => {
	window.clearTimeout(retryTimer);
	window.clearTimeout(phaseTimer);
	if (!event.persisted) window.clearTimeout(popupTimer);
	if (popup?.open) popup.close();
});
syncPhases();
