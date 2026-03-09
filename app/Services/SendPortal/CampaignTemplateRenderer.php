<?php

namespace App\Services\SendPortal;

use App\Models\SendPortal\Campaign;
use App\Models\SendPortal\CampaignMessage;
use App\Models\SendPortal\Subscriber;

class CampaignTemplateRenderer
{
    public function __construct(
        protected TemplatePlaceholderService $placeholderService,
        protected TrackingLinkService $trackingLinkService
    ) {
    }

    public function render(Campaign $campaign, Subscriber $subscriber, ?CampaignMessage $message = null): array
    {
        $html = (string) ($campaign->html_content ?: $campaign->template?->html_content ?: '');
        $text = (string) ($campaign->text_content ?: $campaign->template?->text_content ?: '');

        $values = array_merge($this->placeholderService->sampleValues(), [
            'recipient_email' => $subscriber->email,
            'campaign_name' => $campaign->name,
        ]);

        if ($message) {
            $values['unsubscribe_url'] = $this->trackingLinkService->unsubscribeUrl($message);
        }

        $renderedHtml = $this->placeholderService->render($html, $values);
        $renderedText = $this->placeholderService->render($text, $values);

        if ($message) {
            $renderedHtml = $this->trackingLinkService->rewriteHtmlLinks($message, $renderedHtml);
            $renderedHtml = $this->trackingLinkService->appendOpenPixel($message, $renderedHtml);
        }

        return [
            'subject' => $this->placeholderService->render((string) $campaign->subject, $values),
            'html' => $renderedHtml,
            'text' => $renderedText,
        ];
    }
}