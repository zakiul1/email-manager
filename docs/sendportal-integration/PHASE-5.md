# Phase 5 - Template manager

## Goal
Build a modern native template manager with CRUD, placeholder validation, sanitized preview, duplicate, and test-send support.

## What Phase 5 adds
- Template list page with search and status filtering
- Template create/edit page
- Placeholder helper panel
- Placeholder detection and unsupported placeholder validation
- Desktop/mobile preview
- Duplicate template action
- Test-send screen and flow
- Template test logging through `sp_template_tests`

## New tables
- `sp_template_tests`
- extended `sendportal_templates` fields:
  - `preheader`
  - `status`
  - `usage_count`
  - `version_notes`
  - `builder_meta`
  - `last_test_sent_at`

## Main services
- `TemplatePlaceholderService`
- `TemplatePreviewSanitizer`
- `TemplateTestSendService`

## Main UI pages
- `/sendportal/templates`
- `/sendportal/templates/create`
- `/sendportal/templates/{template}/edit`
- `/sendportal/templates/{template}/preview`
- `/sendportal/templates/{template}/test-send`

## Validation rules
- required template name, slug, subject, and HTML body
- unsupported placeholders are rejected
- test send requires a valid email recipient
- preview output is sanitized before rendering

## Exit criteria
- templates can be created and edited
- preview works in desktop and mobile mode
- duplicate works
- test-send works
- test logs are created and last test timestamp is updated