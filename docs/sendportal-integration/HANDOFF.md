# SendPortal Integration Handoff

## Project goal
Integrate SendPortal concepts into the existing app as a native admin workspace without duplicating your app’s own auth, user management, and core data ownership.

## What has been built
- SendPortal admin workspace shell
- native routing and sidebar integration
- subscriber bridge and tag/category bridge
- SMTP accounts and SMTP pools
- template manager with preview and test send
- campaign CRUD
- audience preparation
- queued dispatch
- limit-aware SMTP selection
- retry and failover handling
- reporting dashboards
- tracking, unsubscribe, and public webhook endpoints
- authorization hardening and retry scheduling

## Important architecture decisions
- your app remains the system of record for auth and main admin layout
- categories remain the primary audience source
- SendPortal-native tables are used only where needed for campaign delivery
- SMTP sending can use Laravel mailer dynamically per selected account
- public tracking uses campaign message tracking tokens
- provider webhook reconciliation uses provider message ID first, then tracking token fallback

## Critical files
- `routes/sendportal.php`
- `routes/web.php`
- `routes/console.php`
- `bootstrap/providers.php`
- `config/sendportal-integration.php`
- `app/Services/SendPortal/*`
- `app/Livewire/SendPortal/*`
- `docs/sendportal-integration/*`

## If another ChatGPT account continues this project
1. Read all files in `docs/sendportal-integration/`
2. Check current routes with:
   - `php artisan route:list --path=sendportal`
   - `php artisan route:list --path=sp/public`
3. Check schedule:
   - `php artisan schedule:list`
4. Check queue worker and retry command:
   - `php artisan queue:work`
   - `php artisan sendportal:retry-failed-messages`
5. Smoke test:
   - create/edit template
   - create/edit SMTP account
   - create/edit campaign
   - prepare audience
   - dispatch campaign
   - check reports
   - test unsubscribe
   - test webhook

## Remaining recommended work
- provider-specific webhook signature verification
- provider-specific delivery event adapters
- click/open bot filtering
- advanced chart widgets in reports
- stronger role/permission mapping to your app’s actual permission system
- end-to-end feature tests for queue + tracking flow

## Current status
Core implementation is in place. Remaining work is mainly production polish, provider-specific adaptation, and full QA.