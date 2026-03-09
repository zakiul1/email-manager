# SendPortal Operations Guide

## Add SMTP account
1. Open SendPortal > SMTP Accounts
2. Click Create Account
3. Fill host, port, credentials, sender defaults, limits, and status
4. Save
5. Run connection test
6. Confirm account is active and not in cooldown

## Create template
1. Open SendPortal > Templates
2. Click Create Template
3. Fill subject, preheader, HTML, and text body
4. Review placeholders
5. Save
6. Preview and optionally test-send

## Schedule campaign
1. Open SendPortal > Campaigns
2. Click Create Campaign
3. Select template, SMTP pool, and categories or tags
4. Set delivery mode to Schedule
5. Fill scheduled datetime
6. Save
7. Open Audience page and prepare messages
8. Dispatch when ready

## Pause campaign
1. Open campaign detail
2. Click Pause
3. Confirm campaign status changes

## Debug failures
1. Open Reports
2. Open the campaign detail report
3. Review failure reasons, attempts, SMTP account used
4. Check SMTP account usage and cooldown state
5. Run retry command if needed:
   `php artisan sendportal:retry-failed-messages`
6. Review queue worker logs and provider/webhook payloads