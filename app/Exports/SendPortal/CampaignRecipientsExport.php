<?php

namespace App\Exports\SendPortal;

use App\Models\SendPortal\Campaign;
use App\Models\SendPortal\CampaignMessage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CampaignRecipientsExport
{
    public function download(Campaign $campaign, string $filename = null): StreamedResponse
    {
        $filename ??= 'campaign-'.$campaign->id.'-recipients.csv';

        return response()->streamDownload(function () use ($campaign) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'recipient_email',
                'status',
                'smtp_account',
                'subject',
                'sent_at',
                'failed_at',
                'failure_reason',
                'attempt_count',
            ]);

            CampaignMessage::query()
                ->with('smtpAccount')
                ->where('campaign_id', $campaign->id)
                ->orderBy('id')
                ->chunk(500, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->recipient_email,
                            $row->status,
                            $row->smtpAccount?->name,
                            $row->subject,
                            optional($row->sent_at)?->toDateTimeString(),
                            optional($row->failed_at)?->toDateTimeString(),
                            $row->failure_reason,
                            $row->attempt_count,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}