# Phase 1 - Foundation and shell integration

## Goal
Create a clean SendPortal workspace inside the existing app without duplicating authentication, user management, or the current Email Manager shell.

## What was added
- Added a dedicated `/sendportal` route group.
- Added a `SendPortal` entry to the main application sidebar.
- Added a new SendPortal layout with a second sidebar that matches the current app style.
- Added a shared navigation definition for future SendPortal modules.
- Added a SendPortal dashboard placeholder that tracks the roadmap and explains the integration intent.
- Added integration config for route prefix, layout, middleware, and future feature flags.

## Files introduced
- `config/sendportal-integration.php`
- `routes/sendportal.php`
- `app/Support/SendPortal/Navigation.php`
- `app/Livewire/SendPortal/Dashboard/Index.php`
- `resources/views/layouts/sendportal.blade.php`
- `resources/views/layouts/sendportal/sidebar.blade.php`
- `resources/views/livewire/sendportal/dashboard/index.blade.php`
- `docs/sendportal-integration/PHASE-1.md`

## Files updated
- `routes/web.php`
- `resources/views/layouts/app/sidebar.blade.php`

## Rules for future phases
- Keep the main app auth and user management as the source of truth.
- Keep the SendPortal area mounted under `/sendportal`.
- Reuse current toast/notification behavior and Flux-based UI patterns.
- Do not add duplicate subscriber/category/suppression logic without a mapping strategy.
- Avoid vendor modifications when package integration begins.

## Exit criteria for Phase 1
- User can see a `SendPortal` menu item in the primary sidebar.
- Clicking it opens a dedicated nested workspace inside the same app.
- The nested workspace shows a second sidebar for future modules.
- No existing Email Manager route or auth behavior is broken.

## Start point for Phase 2
- Install and wire SendPortal Core into the current app.
- Bind package routes into the new shell without enabling duplicate auth/workspace behavior.
- Create the shared integration layer for category mapping, suppression checks, and future SMTP strategy services.