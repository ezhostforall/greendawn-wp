# Modules

Greendawn Core always loads the site-specific homepage/footer/enquiry fields, page fields, EV Chargers and Team Members. `Services` and `Diagnostics` remain optional through `greendawn_core_enabled_modules`; extensions can register through `greendawn_core_available_modules`.

The EV Charger module owns `evcharger`, `brands`, `power_outputs` and the bundled original SCF specification schema. Team Members owns non-public `team_member` records and structured profile fields. See [Internal-page content foundation](../../../../docs/internal-pages.md) for installation, capabilities and the Neel-owned charger import sequence.

Business logic and outbound integrations belong in Core, presentation in the theme. Modules that send data off-site must check `Plugin::outbound_allowed()`.

Guides owns `gdguide`, the non-public `gdguide_topic` taxonomy, and the structured Guide/hub/region field contract. It does not seed content or replace existing Page URLs. See [Guides foundation](../../../../docs/guides-content-model.md) for native readers, validated SCF writes, revisions, derived search data and the later import/template boundary.
