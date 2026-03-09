<?php

namespace App\Services\SendPortal;

use App\Models\SendPortal\Campaign;
use App\Models\SendPortal\CampaignMessage;
use Illuminate\Support\Facades\DB;

class CampaignPreparationService
{
    public function __construct(
        protected CampaignAudienceResolver $audienceResolver,
        protected ActivityLogService $activityLogService
    ) {
    }

    public function prepare(Campaign $campaign): array
    {
        $campaign->loadMissing(['template', 'smtpPool']);

        $subscribers = $this->audienceResolver->resolve($campaign);

        $prepared = 0;
        $suppressed = 0;

        DB::transaction(function () use ($campaign, $subscribers, &$prepared, &$suppressed) {
            CampaignMessage::query()
                ->where('campaign_id', $campaign->id)
                ->delete();

            foreach ($subscribers as $subscriber) {
                if ($subscriber->is_suppressed) {
                    $suppressed++;

                    continue;
                }

                CampaignMessage::query()->create([
                    'campaign_id' => $campaign->id,
                    'subscriber_id' => $subscriber->id,
                    'template_id' => $campaign->template_id,
                    'smtp_pool_id' => $campaign->smtp_pool_id,
                    'status' => 'pending',
                    'recipient_email' => $subscriber->email,
                    'subject' => $campaign->subject,
                    'html_body' => $campaign->html_content ?: $campaign->template?->html_content,
                    'text_body' => $campaign->text_content ?: $campaign->template?->text_content,
                    'meta' => [
                        'prepared_from_audience_type' => $campaign->audience_type,
                    ],
                ]);

                $prepared++;
            }

            $campaign->update([
                'recipient_count' => $prepared,
                'failed_count' => 0,
                'sent_count' => 0,
                'queued_at' => null,
                'meta' => array_merge($campaign->meta ?? [], [
                    'suppressed_count' => $suppressed,
                    'prepared_at' => now()->toDateTimeString(),
                ]),
            ]);
        });

        $this->activityLogService->log('campaign.prepared', $campaign, [
            'prepared' => $prepared,
            'suppressed' => $suppressed,
        ]);

        return [
            'prepared' => $prepared,
            'suppressed' => $suppressed,
            'total' => $prepared + $suppressed,
        ];
    }
}