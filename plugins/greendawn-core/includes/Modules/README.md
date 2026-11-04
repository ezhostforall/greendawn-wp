# Modules

Each module implements `Module_Interface` and is enabled per site with the
`greendawn_core_enabled_modules` filter. Register custom modules through
`greendawn_core_available_modules`.

Planned, **not implemented** extension areas (add them as new classes here when a project
needs them): form handling, lead storage, email/webhook delivery (must check
`Plugin::outbound_allowed()`), marketing settings, client-specific integration hooks.
