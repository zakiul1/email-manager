# Phase 11 - Tracking, unsubscribe, and public endpoints

## Goal
Add public open/click tracking, tracked unsubscribe links, provider webhook reconciliation, and engagement reporting fields.

## What Phase 11 adds
- tracking token per campaign message
- delivered/opened/clicked/unsubscribed timestamps
- bounced/complained timestamps
- open and click counters
- provider message ID and provider event tracking
- public open pixel endpoint
- public tracked click redirect endpoint
- public unsubscribe endpoint
- public webhook endpoint
- unsubscribe success and failed pages
- campaign detail reporting for opens, clicks, and unsubscribes

## Main services
- `TrackingLinkService`
- `CampaignTrackingService`
- `CampaignWebhookService`

## Main public routes
- `/sp/public/open/{token}`
- `/sp/public/click/{token}`
- `/sp/public/unsubscribe/{token}`
- `/sp/public/webhook`

## Key rules
- each campaign message gets its own tracking token
- unsubscribe uses the same message token
- click tracking redirects only to valid URLs
- unsubscribe suppresses the subscriber through the existing suppression system
- webhook payloads should reconcile against provider message ID first, then tracking token fallback

## Exit criteria
- sent campaign messages include tracking token data
- open pixel updates open counts
- click redirect updates click counts
- unsubscribe page suppresses the subscriber
- webhook updates message delivery and failure state
- campaign detail report shows engagement metrics