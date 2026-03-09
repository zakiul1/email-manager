# Phase 9 - Retry and failover control

## Goal
Improve delivery resilience with retry jobs, SMTP health tracking, cooldown handling, and failover selection.

## What Phase 9 adds
- SMTP health service
- automatic failure counting
- cooldown handling
- paused/failing sender state tracking
- retry-ready failed message rows
- retry command
- SMTP failover across multiple eligible accounts
- reporting updates for SMTP health

## Main services
- `SmtpHealthService`
- updated `CampaignSendService`
- updated `SmtpPoolSelectorService`

## Main jobs and commands
- `RetryFailedCampaignMessageJob`
- `sendportal:retry-failed-messages`

## Key rules
- failed SMTP accounts accumulate failure counts
- accounts enter cooldown or pause after repeated failures
- pool selector skips cooling-down and paused accounts
- failed messages get retry timestamps
- retry command requeues eligible failed messages

## Exit criteria
- failed sends create retry timestamps
- retry command queues failed messages again
- failing SMTP accounts enter cooldown
- other healthy accounts are selected for later sends