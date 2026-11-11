# Greendawn consent and enquiry tracking

Greendawn Core exposes the environment settings. The theme reads Complianz Free 7.5.7 and loads GTM only after an explicit Statistics or Marketing grant. Contact Form 7 still owns delivery and inline confirmation. Install the theme and Core from the same release.

## WordPress settings

On staging, retain the UK opt-in banner with independently selectable Statistics and Marketing. Disable Complianz GTM injection and Complianz Google Consent Mode. Check that Rank Math, Site Kit and legacy snippets do not inject tracking. Keep the Statistics exemption (forced Statistics without consent) off.

Add the following to the **private** site config before the WordPress bootstrap. Use an isolated GTM staging container with test destinations only. Leave staging's container empty for local data layer inspection without a GTM request.

```php
define(
	'GREENDAWN_CORE_TRACKING',
	array(
		'production' => array(
			'host'      => 'greendawn.co.uk',
			'container' => 'GTM-W47CCRXX',
		),
		'staging' => array(
			'host'      => 'gd.ezhostforall.com',
			'container' => '', // Fill in the isolated test container ID.
		),
	)
);
```

Set `WP_ENVIRONMENT_TYPE` to the actual environment. The configured hostname must match WordPress Home URL and the browser hostname. Core refuses to use the production host or production container on staging. Local, development and unconfigured sites make no GTM request. There is no noscript iframe. Purge WordPress, page and edge caches after changing the config. Remove the constant and purge caches to disable the container.

## GTM workspace contract

The page pushes `greendawn_consent_state` with `gd_consent_revision`, and boolean `gd_statistics` and `gd_marketing`. It waits for the ready callback before releasing eligible early conversions. Configure this contract in the unpublished workspace and the isolated staging container before testing.

Create data layer variables with those exact names, plus `gd_event_statistics` and `gd_event_marketing`. Use three instances of a sandboxed consent tag template with the following modes. The example assumes template fields called `mode`, `revision`, `statistics` and `marketing`. Map the last three to their data layer variables. Use a text/select field for mode and variable-compatible fields for the data values.

```js
const setDefaultConsentState = require('setDefaultConsentState');
const updateConsentState = require('updateConsentState');
const createQueue = require('createQueue');
const callInWindow = require('callInWindow');

if (data.mode === 'default') {
  setDefaultConsentState({
    analytics_storage: 'denied',
    ad_storage: 'denied',
    ad_user_data: 'denied',
    ad_personalization: 'denied',
  });
} else if (data.mode === 'update') {
  const ads = data.marketing === true ? 'granted' : 'denied';
  updateConsentState({
    analytics_storage: data.statistics === true ? 'granted' : 'denied',
    ad_storage: ads,
    ad_user_data: ads,
    ad_personalization: 'denied',
  });
  createQueue('dataLayer')({
    event: 'greendawn_consent_ready',
    gd_consent_revision: data.revision,
  });
} else if (data.mode === 'ready') {
  callInWindow('greendawn_tracking_ready', data.revision);
}
data.gtmOnSuccess();
```

- Grant the template write access to the four Google consent types, read/write global access to the `dataLayer` queue, and execute access to `greendawn_tracking_ready`. Consent tags themselves have no additional consent requirement.
- Trigger **default** on **Consent Initialization – All Pages**, **update** on `greendawn_consent_state`, and **ready** on `greendawn_consent_ready`. Leave the ready callback at normal priority or below the base tags. The ready event is evaluated after GTM applies the update.
- Fire the destination base tags on `greendawn_consent_ready`, **Once per page**, with the exact destination hostname and required category. Use `analytics_storage` for GA4 and `ad_storage` plus `ad_user_data` for paid destinations. Use only a test GA4 stream or an isolated test tag on staging.
- Microsoft base on production is UET `187222838` with queue `uetq`. On the configured production host, WordPress queues a denied UET default before loading GTM and sends UET the updated choice before each GTM consent state, including on withdrawal. Use this as the single Microsoft consent sender. If the UET template exposes a separate inherited-consent sender, leave it off and verify the explicit signal in UET Tag Helper. Keep enhanced conversions off in both the UET tag and goal `160600238`.
- Set measurement event tags to **Once per event**. Require the appropriate current GTM consent **and** the corresponding event flag to equal `true`. The flags reflect whether consent was present when the request started and remained in effect through confirmation. Never attach a paid tag to `support_request`.
- Keep the eight legacy Version 17 tags paused in the rebuild. Confirm the Microsoft base tag is saved with its new firing rule and leave production publishing and bidding changes for the release step.

## Form events and acceptance

The browser emits `generate_lead` for confirmed sales placements and `support_request` for Customer Care with Statistics consent. Explicit sales placements are Contact, About, Workplace, Enquire, Business EV Charging Installations and the React drawer. Aftercare's inline form and the drawer's explicit Aftercare intent are held out of measurement pending classification. Where an editor inserts a form outside these placements, review its routing before adding measurement. Event fields are `form_location`, `lead_type`, `cta_location`, `charger_slug` and the two event consent flags.

On the staging release, verify with Network, the storage inspector, GTM Preview and the actual inboxes. Use the isolated test container for destination verification. Before a choice and after Reject all, check both the first navigation and a reload for no GTM or optional vendor request. Verify independent category choices, same-page acceptance, saved consent on reload and withdrawal after a vendor was already active. If an already-loaded vendor continues sending optional measurements after withdrawal, resolve that before production release. Check that Marketing withdrawal clears first-touch session storage and form fields, and that enquiry delivery still works.

Submit a sales enquiry, charger quote and support request. Confirm one event and the intended inbox for each, then test invalid, failed and repeated submissions. Use the live canonical hostname for the final Microsoft UET and attributed **All conversions** check before switching bidding.
