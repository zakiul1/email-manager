# Phase 3 - Subscribers, tags, and category bridge

## Goal
Connect the existing Email Manager category/email system to the native SendPortal subscriber and tag system.

## What Phase 3 adds
- Native subscriber listing screen
- Native tag listing screen
- Category Bridge screen
- Subscriber sync from `email_addresses`
- Tag sync from `categories`
- Category ↔ tag bridge table
- Suppression-aware subscriber upsert service
- Category sync command

## New tables
- `sendportal_category_tag_links`
- extended `sendportal_subscribers` fields:
  - `email_address_id`
  - `is_suppressed`
  - `last_synced_at`

## Key sync rules
- `categories` remain the source of truth
- one category can map to one SendPortal tag
- one email address can map to one native subscriber row
- suppression rules are reused from the Email Manager app
- suppressed subscribers remain tracked but are marked as suppressed

## Main services
- `SubscriberUpsertService`
- `CategoryTagSyncService`

## Main UI pages
- `/sendportal/subscribers`
- `/sendportal/tags`
- `/sendportal/category-bridge`

## Command
- `php artisan sendportal:sync-category-bridge`
- `php artisan sendportal:sync-category-bridge {categoryId}`

## Exit criteria
- Category Bridge page loads
- subscribers can be created by sync
- tags can be created by sync
- suppressed emails become suppressed subscribers
- category sync updates counts and last sync timestamps