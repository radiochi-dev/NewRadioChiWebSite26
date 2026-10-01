<?php

namespace App\Actions\Legal;

use App\Actions\Legacy\ImportLegacyContentAction;
use App\Models\LegalDocument;
use App\Models\Setting;
use App\Support\NewsletterLegalContent;
use App\Support\NewsletterLegalConsent;
use Illuminate\Support\Facades\DB;

class SyncNewsletterTransparencyLegalDocsAction
{
    public function execute(): array
    {
        return DB::transaction(function (): array {
            foreach (['terms', 'privacy', 'cookies'] as $position => $documentType) {
                $document = LegalDocument::query()->updateOrCreate(
                    ['slug' => $documentType],
                    [
                        'document_type' => $documentType,
                        'version' => NewsletterLegalConsent::legalDocumentVersion(),
                        'position' => $position + 1,
                        'is_published' => true,
                        'published_at' => now(),
                        'settings' => ['source' => 'app/Support/NewsletterLegalContent.php'],
                    ],
                );

                foreach (ImportLegacyContentAction::LOCALES as $locale) {
                    $payload = NewsletterLegalContent::locale($locale)[$documentType] ?? [];

                    $document->translations()->updateOrCreate(
                        ['locale' => $locale],
                        [
                            'title' => $payload['title'] ?? ucfirst($documentType),
                            'summary' => null,
                            'content' => $payload['content'] ?? '',
                            'cta_label' => NewsletterLegalContent::locale($locale)[$documentType.'_button'] ?? null,
                        ],
                    );
                }
            }

            $setting = Setting::query()->updateOrCreate(
                ['group' => 'legal', 'key' => 'buttons'],
                [
                    'type' => 'json',
                    'value' => ['default' => []],
                    'is_translatable' => true,
                    'is_public' => true,
                    'position' => 1,
                    'settings' => ['source' => 'app/Support/NewsletterLegalContent.php'],
                ],
            );

            foreach (ImportLegacyContentAction::LOCALES as $locale) {
                $setting->translations()->updateOrCreate(
                    ['locale' => $locale],
                    ['value' => [
                        'terms_button' => NewsletterLegalContent::locale($locale)['terms_button'] ?? null,
                        'privacy_button' => NewsletterLegalContent::locale($locale)['privacy_button'] ?? null,
                        'cookies_button' => NewsletterLegalContent::locale($locale)['cookies_button'] ?? null,
                    ]],
                );
            }

            return [
                'legal_documents' => LegalDocument::query()->count(),
                'legal_document_translations' => LegalDocument::query()->withCount('translations')->get()->sum('translations_count'),
                'button_translations' => $setting->translations()->count(),
                'version' => NewsletterLegalConsent::legalDocumentVersion(),
            ];
        });
    }
}
