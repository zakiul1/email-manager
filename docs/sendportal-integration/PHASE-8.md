# Phase 8 - Limits and reporting

## Goal
Add sending limits, smarter SMTP account selection, and reporting pages for campaign delivery and SMTP usage.

## What Phase 8 adds
- daily, hourly, and warmup limit checks
- SMTP pool selection that skips accounts outside limits
- usage recording per SMTP account
- reports page
- campaign delivery summary
- SMTP account usage summary

## Main services
- `SmtpAccountLimitService`
- updated `SmtpPoolSelectorService`
- updated `CampaignSendService`

## Main UI pages
- `/sendportal/reports`

## Key rules
- inactive SMTP accounts are never selected
- accounts that exceed daily/hourly/warmup limits are skipped
- fallback uses default account only if it is active and within limits
- report screens summarize sent, failed, and pending delivery states

## Exit criteria
- report page loads
- accounts stop being selected when limits are reached
- SMTP usage counters increase after sending
- campaign stats show sent and failed counts