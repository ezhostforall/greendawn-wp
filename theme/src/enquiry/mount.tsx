import { Component, createRef, useEffect, useImperativeHandle, useRef, useState, type CSSProperties, type ReactNode } from 'react';
import { createRoot } from 'react-dom/client';
import { flushSync } from 'react-dom';
import type { LeadConfig } from './launcher';
import { submitCallback } from './transport';

type Step = 'intent' | 'context' | 'stage' | 'location' | 'contact' | 'success';
const contexts: Record<string, string> = { 'ev-charging': 'siteType', power: 'powerReason', aftercare: 'aftercareIssue' };
const legends: Record<string, string> = { intent: 'What can we help with?', siteType: 'What best describes the site?', aftercareIssue: 'What are you experiencing?', powerReason: 'What’s prompted the enquiry?', projectStage: 'Where are you in the process?' };
const entryPoints = ['floating-launcher', 'header', 'hero', 'services', 'pricing', 'final-cta', 'footer-cta'];

interface Handle { open: (opener: HTMLElement, entry: string) => void; close: () => void }

class Boundary extends Component<{ children: ReactNode; fallback: string }, { failed: boolean }> {
	state = { failed: false };
	static getDerivedStateFromError() { return { failed: true }; }
	render() {
		return this.state.failed ? <p role="alert">The callback form could not be loaded. <a href={this.props.fallback}>Use our enquiry page</a>.</p> : this.props.children;
	}
}

function App({ config, transport, handleRef }: { config: LeadConfig; transport: HTMLFormElement; handleRef: React.RefObject<Handle | null> }) {
	const dialog = useRef<HTMLDialogElement>(null);
	const form = useRef<HTMLFormElement>(null);
	const body = useRef<HTMLDivElement>(null);
	const opener = useRef<HTMLElement | null>(null);
	const entry = useRef('floating-launcher');
	const inFlight = useRef(false);
	const [opened, setOpened] = useState(false);
	const [step, setStep] = useState<Step>('intent');
	const [history, setHistory] = useState<Step[]>([]);
	const [answers, setAnswers] = useState<Record<string, string>>({});
	const [contact, setContact] = useState<Record<string, string>>({});
	const [errors, setErrors] = useState<Record<string, string>>({});
	const [message, setMessage] = useState('');
	const [submitting, setSubmitting] = useState(false);
	const [direct, setDirect] = useState(false);
	const [started, setStarted] = useState(false);
	const context = contexts[answers.intent ?? ''];
	const steps: Step[] = direct ? ['contact'] : ['intent', ...(context ? ['context' as const] : []), 'stage', 'location', 'contact'];
	const progress = step === 'success' ? 100 : (steps.indexOf(step) + 1) / steps.length * 100;
	const stageLabel = { intent: 'Your requirement', context: 'Site details', stage: 'Project stage', location: 'Site location', contact: 'Callback details', success: 'Complete' }[step];

	function emit(event: string, values = answers): void {
		// Only controlled choices cross the analytics boundary. No PII, URLs or free text.
		document.dispatchEvent(new CustomEvent('greendawn:lead-funnel', { detail: {
			event, source: config.source, entryPoint: entry.current,
			...(values.intent ? { intent: values.intent } : {}),
			...(values.projectStage ? { projectStage: values.projectStage } : {}),
		} }));
	}
	function reset(): void {
		setStep('intent'); setHistory([]); setAnswers({}); setContact({}); setErrors({}); setMessage(''); setDirect(false); setStarted(false);
		transport.reset();
	}
	function close(): void { dialog.current?.close(); }
	useImperativeHandle(handleRef, () => ({
			open: (trigger, point) => {
				opener.current = trigger; entry.current = entryPoints.includes(point) ? point : 'floating-launcher';
				if (step === 'success') reset();
				dialog.current?.showModal(); document.body.classList.add('lead-capture-open'); setOpened(true); emit('lead_tool_opened');
			}, close,
	}));
	useEffect(() => {
		if (!opened) return;
		body.current?.scrollTo(0, 0);
		dialog.current?.querySelector<HTMLElement>('[data-step-heading]')?.focus({ preventScroll: true });
	}, [step, opened]);
	useEffect(() => () => { document.body.classList.remove('lead-capture-open'); }, []);
	function go(next: Step): void { setHistory([...history, step]); setStep(next); setMessage(''); }
	function startCallback(): void { if (!started) { emit('lead_callback_started'); setStarted(true); } }
	function choose(name: string, value: string): void {
		const next = { ...answers, [name]: value };
		if (name === 'intent') { setDirect(false); delete next.siteType; delete next.aftercareIssue; delete next.powerReason; }
		setAnswers(next);
		if (name === 'intent') { emit('lead_intent_selected', next); go(contexts[value] ? 'context' : 'stage'); }
		else go(name === 'projectStage' ? 'location' : 'stage');
	}
	function field(name: string, label: string, type = 'text', autoComplete = '', maxLength = 120) {
		return <label className="lead-capture__field"><span id={`lead-label-${name}`}>{label}</span>
			<input name={name} type={type} autoComplete={autoComplete} maxLength={maxLength} value={contact[name] ?? ''} required={['firstName', 'phone', 'company', 'email'].includes(name)} aria-labelledby={`lead-label-${name}`} aria-invalid={!!errors[name]} aria-describedby={errors[name] ? `lead-error-${name}` : undefined} onChange={(e) => { setContact({ ...contact, [name]: e.target.value }); setErrors({ ...errors, [name]: '' }); }} />
			{errors[name] && <span className="lead-capture__error" id={`lead-error-${name}`}>{errors[name]}</span>}
		</label>;
	}
	function focusErrors(next: Record<string, string>): void {
		setErrors(next);
		setMessage('Please check the highlighted fields.');
		requestAnimationFrame(() => {
			const control = form.current?.elements.namedItem(Object.keys(next)[0] ?? '');
			if (control instanceof HTMLElement) control.focus();
			else if (control instanceof RadioNodeList) (control[0] as HTMLElement | undefined)?.focus();
		});
	}
	async function submit(): Promise<void> {
		if (inFlight.current) return;
		const next: Record<string, string> = {};
		for (const [name, label] of Object.entries({ firstName: 'your first name', company: 'your company name', email: 'your email address' })) {
			if (!contact[name]?.trim()) next[name] = `Enter ${label}.`;
		}
		const email = form.current?.elements.namedItem('email');
		if (email instanceof HTMLInputElement && email.value && !email.validity.valid) next.email = 'Enter a valid email address.';
		const phone = contact.phone?.trim() ?? '';
		if (!/^[+\d][\d\s()./-]*(?:\s?(?:x|ext\.?)\s?\d{1,6})?$/i.test(phone) || phone.replace(/\D/g, '').length < 7 || phone.replace(/\D/g, '').length > 18) next.phone = 'Enter a telephone number we can use for the callback.';
		if (!contact.callbackPreference) next.callbackPreference = 'Choose when you would prefer us to call.';
		if (Object.keys(next).length) { focusErrors(next); return; }
		inFlight.current = true; setSubmitting(true); setMessage(''); setErrors({});
		const values: Record<string, string> = { ...answers, ...contact, source: config.source, entryPoint: entry.current, pagePath: window.location.pathname.slice(0, 240) };
		for (const name of ['intent', 'projectStage', 'siteType', 'aftercareIssue', 'powerReason', 'location', 'preferredCallbackTime', 'notes', 'website', 'utmSource', 'utmMedium', 'utmCampaign']) values[name] ??= '';
		if (values.callbackPreference !== 'scheduled') values.preferredCallbackTime = '';
		const params = new URLSearchParams(window.location.search);
		for (const [key, query] of [['utmSource', 'utm_source'], ['utmMedium', 'utm_medium'], ['utmCampaign', 'utm_campaign']] as const) values[key] = params.get(query)?.slice(0, 120) ?? '';
		try {
			const response = await submitCallback(transport, values);
			if (response.status === 'mail_sent') {
				emit('lead_callback_submitted'); setStep('success'); setContact({}); setAnswers({});
			} else if (response.invalid_fields?.length) {
				focusErrors(Object.fromEntries(response.invalid_fields.map(({ field: name, message: error }) => [name, error])));
			} else setMessage('Your request could not be sent. Please try again or call us directly.');
		} catch (error) { setMessage(error instanceof Error ? error.message : 'We could not confirm delivery. Please call us.'); }
		finally { inFlight.current = false; setSubmitting(false); }
	}

	const choiceName = step === 'intent' ? 'intent' : step === 'context' ? (context ?? 'siteType') : 'projectStage';
	return <dialog ref={dialog} className="lead-capture__dialog" id="lead-capture-dialog" aria-labelledby="lead-capture-title" onClose={() => { document.body.classList.remove('lead-capture-open'); setOpened(false); opener.current?.focus(); }} onClick={(e) => { if (e.target === e.currentTarget) close(); }}>
		<section className="lead-capture__panel" style={{ '--lead-progress': progress } as CSSProperties}>
			<form ref={form} className="lead-capture__form" noValidate onSubmit={(e) => { e.preventDefault(); if (step === 'contact') void submit(); else if (step === 'location') { startCallback(); go('contact'); } }}>
				<header className="lead-capture__header">
					{history.length > 0 && step !== 'success' && <button className="lead-capture__icon-button" data-lead-back type="button" disabled={submitting} onClick={() => { setStep(history[history.length - 1] ?? 'intent'); setHistory(history.slice(0, -1)); setErrors({}); setMessage(''); }}><span aria-hidden="true">←</span><span>Back</span></button>}
					<div className="lead-capture__brand"><span>Greendawn</span><strong id="lead-capture-title">Request a callback</strong></div>
					<button className="lead-capture__icon-button" data-lead-close type="button" aria-label="Close callback form" onClick={close}><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg></button>
				</header>
				<div className="lead-capture__progress" aria-hidden="true"><svg viewBox="0 0 180 30" preserveAspectRatio="none"><path className="lead-capture__progress-track" d="M3 27C35 3 125 3 177 27" pathLength="100" /><path className="lead-capture__progress-value" d="M3 27C35 3 125 3 177 27" pathLength="100" /></svg><span>{stageLabel}</span></div>
				<div className="screen-reader-text" role="status" aria-live="polite">{opened ? stageLabel : ''}</div>
				<div className="lead-capture__body" ref={body}>
					<section className={`lead-capture__step${step === 'success' ? ' lead-capture__success' : ''}`}>
						{['intent', 'context', 'stage'].includes(step) && <><fieldset><legend tabIndex={-1} data-step-heading>{legends[choiceName]}</legend><p className="lead-capture__intro">Choose the closest match to continue. You can add detail before requesting your callback.</p><div className="lead-capture__choices lead-capture__choices--stacked">{Object.entries(config.choices[choiceName] ?? {}).map(([value, label]) => <button className="lead-capture__option" type="button" key={value} aria-pressed={answers[choiceName] === value} onClick={() => choose(choiceName, value)}>{label}<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7" /></svg></button>)}</div></fieldset>{step === 'intent' && <div className="lead-capture__step-actions"><button className="lead-capture__text-button" type="button" onClick={() => { setDirect(true); startCallback(); go('contact'); }}>Just request a callback</button></div>}</>}
						{step === 'location' && <><div className="lead-capture__heading-group"><h2 tabIndex={-1} data-step-heading>Where is the site?</h2><p className="lead-capture__intro">Optional, but useful for routing your enquiry.</p></div>{field('location', 'Town, city or postcode (optional)', 'text', 'postal-code')}<div className="lead-capture__step-actions"><button className="lead-capture__primary" type="button" onClick={() => { startCallback(); go('contact'); }}>Continue</button><button className="lead-capture__text-button" type="button" onClick={() => { setContact({ ...contact, location: '' }); startCallback(); go('contact'); }}>Skip for now</button></div></>}
						{step === 'contact' && <>
							<div className="lead-capture__heading-group"><h2 tabIndex={-1} data-step-heading>How should we reach you?</h2><p className="lead-capture__intro">Greendawn supports commercial sites. All four contact fields are required so we can route the enquiry.</p></div>
							<div className="lead-capture__field-grid">{field('firstName', 'First name', 'text', 'given-name', 80)}{field('phone', 'Telephone number', 'tel', 'tel', 40)}{field('company', 'Company name', 'text', 'organization')}{field('email', 'Email', 'email', 'email', 160)}</div>
							<fieldset className="lead-capture__callback-fieldset"><legend>When would you prefer a call?</legend><div className="lead-capture__choices lead-capture__choices--compact">{Object.entries(config.choices.callbackPreference ?? {}).map(([value, label]) => <label key={value} className="lead-capture__option lead-capture__option--compact"><input type="radio" name="callbackPreference" value={value} checked={contact.callbackPreference === value} required aria-invalid={!!errors.callbackPreference} aria-describedby={errors.callbackPreference ? 'lead-error-callbackPreference' : undefined} onChange={() => { setContact({ ...contact, callbackPreference: value, preferredCallbackTime: '' }); setErrors({ ...errors, callbackPreference: '' }); }} /><span>{label}</span></label>)}</div>{errors.callbackPreference && <p className="lead-capture__error" id="lead-error-callbackPreference">{errors.callbackPreference}</p>}</fieldset>
							{contact.callbackPreference === 'scheduled' && <>{field('preferredCallbackTime', 'Suggested date and time (optional)', 'datetime-local', '', 30)}<p className="lead-capture__intro">We’ll confirm availability. Times are interpreted in UK local time.</p></>}
							<details className="lead-capture__details"><summary>Add project details <span>Optional</span></summary><div className="lead-capture__details-body"><label className="lead-capture__field"><span>Anything useful for us to know?</span><textarea name="notes" rows={4} maxLength={1500} value={contact.notes ?? ''} onChange={(e) => setContact({ ...contact, notes: e.target.value })} /></label></div></details>
							<label className="lead-capture__honeypot" aria-hidden="true">Leave this empty<input name="website" type="text" tabIndex={-1} autoComplete="off" value={contact.website ?? ''} onChange={(e) => setContact({ ...contact, website: e.target.value })} /></label>
							<p className="lead-capture__privacy">We’ll use these details to respond to this enquiry. Read our <a href={config.privacyUrl}>privacy policy</a>.</p>
						</>}
						{step === 'success' && <><span className="lead-capture__success-mark" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="m13 25 7 7 15-17" /></svg></span><h2 tabIndex={-1} data-step-heading>Thank you. Your request has been sent.</h2><p>We’ll contact you about your enquiry. Suggested callback times are preferences; we’ll confirm availability.</p><div className="lead-capture__step-actions"><button className="lead-capture__primary" type="button" onClick={close}>Close</button><button className="lead-capture__text-button" type="button" onClick={reset}>Start another request</button></div></>}
						{message && <p className="lead-capture__submit-error" role="alert">{message}</p>}
					</section>
				</div>
				{step === 'contact' && <footer className="lead-capture__contact-actions"><button className="lead-capture__primary" type="submit" disabled={submitting} aria-busy={submitting}>{submitting ? 'Sending request…' : 'Request callback'}</button>{config.phoneUrl && <a className="lead-capture__text-button" href={config.phoneUrl}>Or call {config.phone}</a>}</footer>}
			</form>
		</section>
	</dialog>;
}

export function mountLeadCapture(element: HTMLElement, transport: HTMLFormElement, config: LeadConfig) {
	const root = createRoot(element);
	const handle = createRef<Handle>();
	const fallback = config.fallbackUrl;
	flushSync(() => root.render(<Boundary fallback={fallback}><App config={config} transport={transport} handleRef={handle} /></Boundary>));
	return {
		open: (trigger: HTMLElement, entry: string) => {
			if (!handle.current) throw new Error('Callback form is unavailable');
			handle.current.open(trigger, entry);
		},
		close: () => handle.current?.close(),
		destroy: () => root.unmount(),
	};
}
