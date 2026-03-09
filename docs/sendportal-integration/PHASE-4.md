# Phase 4 - SMTP accounts and SMTP pools foundation

## Goal
Create a clean SMTP management layer with secure account storage, validation, connection testing, and reusable SMTP pools.

## What Phase 4 adds
- SMTP account CRUD
- encrypted SMTP password storage
- SMTP connection test action
- SMTP pool CRUD
- SMTP pool membership management
- activity logging foundation
- navigation updates for SMTP modules

## New tables
- `sp_smtp_accounts`
- `sp_smtp_pools`
- `sp_smtp_pool_accounts`
- `sp_activity_logs`

## Main models
- `SmtpAccount`
- `SmtpPool`
- `SmtpPoolAccount`
- `ActivityLog`

## Main services
- `SmtpAccountEncryption`
- `SmtpConnectionTestService`
- `ActivityLogService`

## Main UI pages
- `/sendportal/smtp-accounts`
- `/sendportal/smtp-accounts/create`
- `/sendportal/smtp-pools`
- `/sendportal/smtp-pools/create`

## Validation rules
- SMTP accounts require unique valid structure through field validation
- pool memberships cannot contain duplicate SMTP accounts
- membership weight must be positive
- max percent must be between 1 and 100 when provided

## Exit criteria
- SMTP account create/edit works
- encrypted password is stored
- SMTP connection test returns success or error toast
- SMTP pool create/edit works
- pool memberships save correctly
- SMTP pool list shows membership counts