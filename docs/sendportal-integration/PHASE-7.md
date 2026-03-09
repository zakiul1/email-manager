# Phase 7 - Audience engine, message preparation, and queued dispatch

## Goal
Resolve campaign audiences from categories and tags, apply suppression checks, prepare campaign message rows, and queue sending jobs.

## What Phase 7 adds
- campaign audience page
- audience stats from category/tag sources
- suppression-aware audience resolution
- message preparation action
- creation of pending `sendportal_campaign_messages` rows
- campaign recipient count updates
- queued dispatch action
- queued per-message sending jobs
- SMTP pool based account selection
- campaign and message status updates during sending

## Main services
- `CampaignAudienceResolver`
- `CampaignPreparationService`
- `SmtpPoolSelectorService`
- `CampaignTemplateRenderer`
- `CampaignSendService`

## Main jobs
- `DispatchCampaignJob`
- `SendCampaignMessageJob`

## Main UI pages
- `/sendportal/campaigns/{campaign}/audience`

## Key rules
- categories remain the primary audience source
- tags are supported through synced SendPortal-native tag membership
- suppressed subscribers are excluded from prepared message rows
- dispatch only works after preparation
- each prepared message is sent through a selected SMTP account

## Exit criteria
- audience page loads
- audience counts show total, active, and suppressed
- prepare action creates pending campaign messages
- dispatch queues send jobs
- sent and failed counts update on campaign
- message rows update with sent/failed status