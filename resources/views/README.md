# View Navigation Map (Non-Breaking)

Views are already mostly modular. Keep using this domain-first pattern:

- `resources/views/masters/...`
- `resources/views/modules/project-management/...`
- `resources/views/modules/procurement/...`
- `resources/views/settings/...`
- `resources/views/settings/rbac/...`
- `resources/views/partials/...`

Suggested convention for new pages:

- Each feature gets:
  - `index.blade.php` (listing/main screen)
  - `view.blade.php` (details)
  - optional partials folder: `_partials/`

Example:

- `resources/views/modules/procurement/purchase-order/index.blade.php`
- `resources/views/modules/procurement/purchase-order/view.blade.php`
- `resources/views/modules/procurement/purchase-order/_partials/form.blade.php`

No existing view path was changed by this file.
