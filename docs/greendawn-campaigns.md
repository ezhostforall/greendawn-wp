# Greendawn campaigns

Install the matching theme and Greendawn Core packages together. Secure Custom Fields enables the campaign editor fields. Published records render from their saved metadata even if the editor plugin is temporarily disabled.

1. On staging, open **Settings → Greendawn Core → Campaigns** and choose **Publish and feature the Workplace Charging Scheme campaign**. This creates the initial record at `/workplace-charging-grant/` with the approved copy and dates. It refuses to take over an existing Page or Post URL. Where the record already exists, select it in **Featured campaign**.
2. Edit the record from **Campaigns**. Use **Landing page → Hero image** to select the hero photograph. Existing Featured Image selections and the packaged campaign photograph provide fallbacks when this field is empty. The date tokens shown beside eligible fields insert the saved UK planning and expiry dates. Other published campaign records use `/campaigns/{slug}/`. Selecting **None** removes the featured notices while the landing pages remain available.
3. Confirm the exact landing URL on staging and preserve Google and Microsoft advertising parameters in the final ad URLs. Enable the existing managed callback drawer and confirm native Contact Form 7 delivery. The new optional charger count and campaign reference appear in the main internal callback notification when supplied. The campaign reference identifies the enquiry independently of advertising consent.
4. After staging checks, repeat the publication and featured selection on production. Purge SiteGround Dynamic Cache after the initial upgrade and verify a normal cached visit to a public page. SiteGround Speed Optimizer, when active, is called on featured selection changes, campaign saves and date boundaries. Keep WordPress scheduled events running. Browsers also apply the embedded UK phase boundaries to already cached markup.
5. Confirm the planning phase through 31 January, the availability phase from 1 February, and the evergreen page from 1 April. Verify both timer and scroll triggers, including deferral behind the mobile menu, cookie banner or callback drawer. Verify advertising attribution with Marketing accepted and declined. The pop-up uses session storage per campaign and browser tab.

If the update needs to be rolled back, select **None** and purge the page cache before restoring the previous matched theme and Core packages.


## Structured landing pages

The campaign editor includes repeatable funding figures, eligibility points, conversation-card points, process steps and FAQs, plus closing-enquiry and guidance-source copy. Optional sections with no usable rows are omitted. Page layout uses the same shared width and gutter tokens as the header and other Greendawn pages. Content renders on the server and FAQ accordions work without JavaScript.

FAQ rows can appear while the promotion is active, always, or after expiry. Process steps have separate evergreen headings and copy; populate these to retain a step after expiry. The funding summary and eligibility checklist are promotional and disappear at expiry. The archived page retains relevant existing-voucher information and the ordinary workplace enquiry CTA. Date tokens also work inside repeatable text fields.

### Existing Workplace campaign upgrade

After installing the matching theme and Core update, visit any WordPress admin screen as an administrator. The existing Workplace campaign receives the approved content expansion once. It keeps its ID, URL, publication status, featured selection, dates and selected hero image. Only missing fields or scalar copy still matching the original campaign defaults receive replacements. Deliberately empty fields and customised copy are retained. Edited legacy grant/voucher answers are carried into the repeatable FAQs; the original metadata remains available for rollback.

The per-record content version prevents later updates or repeated setup actions from resetting edits. New Workplace campaign records receive the expanded content during initial provisioning. Workplace-specific defaults are not applied to other campaigns. When an older campaign is opened for editing, its own legacy FAQs are copied into the repeatable editor without replacing its copy. If an editor has customised the original guide heading or intro, review those retained fields alongside the new eligibility checklist.

### Verification

Run `pnpm check` and the campaign browser tests against the disposable integration WordPress. `tests/integration/campaign-content.php` verifies the content upgrade, edited and empty-field preservation, stable URL/featured selection, SCF repeater editing, repeat runs, unrelated campaigns, escaping and unauthorised requests. The integration setup runs this once with SCF and once with SCF skipped. Browser coverage includes 1920, 1440, 1024, 768, 393 and 320px alignment, native FAQs without JavaScript, cached/server expiry transitions and the existing CF7/consent/attribution paths.
