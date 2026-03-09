# Phase 6 - Campaign foundation

## Goal
Build the native campaign foundation: CRUD, preview, scheduling fields, duplicate, and status transitions without sending any mail yet.

## What Phase 6 adds
- Campaign create/edit page
- Campaign list page
- Campaign detail page
- Campaign preview page
- Duplicate campaign action
- Pause / activate / cancel actions
- Audience source definition using categories or tags
- Scheduling-ready campaign fields

## New tables used
- `sendportal_campaigns`
- `sendportal_campaign_audiences`
- `sendportal_campaign_messages`

## Main models
- `Campaign`
- `CampaignAudience`
- `CampaignMessage`

## Main UI pages
- `/sendportal/campaigns`
- `/sendportal/campaigns/create`
- `/sendportal/campaigns/{campaign}`
- `/sendportal/campaigns/{campaign}/edit`
- `/sendportal/campaigns/{campaign}/preview`

## Validation rules
- required name and subject
- delivery mode `schedule` requires `scheduled_at`
- audience type must be `category` or `tag`
- at least one audience source must be selected
- selected audience IDs must exist in the matching table

## Important phase rule
- no actual mail sending yet
- no recipient snapshotting yet
- this phase only stores campaign setup and UI workflow

## Exit criteria
- campaigns can be created and edited
- campaigns can be previewed
- campaigns can be duplicated
- campaigns can be paused, activated, and cancelled
- scheduling fields work in UI