# Production Checklist

## Environment
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` is correct
- [ ] queue worker is running
- [ ] scheduler cron is installed
- [ ] mail sending credentials are valid
- [ ] HTTPS is enabled for public tracking routes

## Queue and scheduler
- [ ] `php artisan queue:work` is supervised
- [ ] `php artisan schedule:run` cron is configured
- [ ] `sendportal:retry-failed-messages` appears in `php artisan schedule:list`

## SMTP
- [ ] all active SMTP accounts tested
- [ ] daily/hourly/warmup limits reviewed
- [ ] default SMTP fallback reviewed
- [ ] failing and paused statuses handled by admin workflow

## Campaigns
- [ ] templates validated
- [ ] audience preparation works
- [ ] suppression checks confirmed
- [ ] dispatch works for a real test campaign

## Tracking
- [ ] open pixel route works
- [ ] click tracking route works
- [ ] unsubscribe route works
- [ ] tracking URLs use production domain

## Webhooks
- [ ] public webhook route reachable
- [ ] provider payload maps correctly
- [ ] provider message IDs are stored when available
- [ ] bounce and complaint events suppress recipients

## Reporting
- [ ] reports page loads
- [ ] campaign detail page loads
- [ ] category performance page loads
- [ ] CSV export works
- [ ] indexes migrated successfully

## Security
- [ ] policies are registered
- [ ] reports are protected
- [ ] SMTP edit screens are protected
- [ ] raw provider errors are not shown directly to users

## Final validation
- [ ] `php artisan optimize:clear`
- [ ] `php artisan migrate --force`
- [ ] `php artisan test`
- [ ] smoke test completed by admin user