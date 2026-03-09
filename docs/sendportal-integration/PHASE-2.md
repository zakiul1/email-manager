# Phase 2 - Native Laravel 12 foundation

## Goal
Replace the failed package-install strategy with a native SendPortal-style implementation inside the current Laravel 12 app.

## Why the package path was dropped
The official SendPortal Core package is not currently compatible with Laravel 12 in this app. Instead of downgrading the host application or maintaining a risky package fork, the project now follows a native implementation path.

## What Phase 2 delivers
- Keeps the SendPortal workspace mounted inside the current admin shell
- Removes package-specific integration code
- Adds native database schema for:
  - subscribers
  - tags
  - subscriber-tag pivot
  - templates
  - email services
  - campaigns
  - campaign audiences
- Adds native Eloquent models for the new SendPortal domain
- Keeps reusable services for:
  - category recipient resolution
  - suppression checking
- Adds placeholder module routes and screens so navigation remains stable

## Keep
- Current auth system
- Current admin layout and sidebar
- Current category and email tables
- Current suppression and domain unsubscribe rules
- Current queue/mail foundations

## Remove
- Package-specific provider
- Package-specific workspace resolver
- Package-specific injected sidebar/header views
- Package-specific route assumptions

## Exit criteria
- `/sendportal` workspace loads normally
- native SendPortal tables migrate successfully
- new SendPortal models load without errors
- sidebar navigation points only to native workspace routes
- project is ready for Phase 3 category bridge work