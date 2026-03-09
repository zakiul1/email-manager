# Phase 10 - Reports

## Goal
Build dashboard KPIs, campaign detail reporting, SMTP usage reporting, category performance reporting, and CSV export with indexed queries.

## What Phase 10 adds
- report indexes for message and campaign tables
- campaign detail report page
- campaign recipient CSV export
- category performance report page
- richer reports dashboard with SMTP health summary
- filtered recipient-level campaign reporting

## Main UI pages
- `/sendportal/reports`
- `/sendportal/reports/campaigns/{campaign}`
- `/sendportal/reports/categories`

## Key rules
- report queries must be indexed and paginated
- large recipient sets must not be rendered without pagination
- reports must reuse canonical categories and email relations where needed
- avoid N+1 queries in report screens

## Exit criteria
- reports dashboard loads
- campaign detail report loads and filters correctly
- category performance report loads
- campaign detail export downloads CSV
- report indexes migrate cleanly