<?php

namespace App\Actions\Newsletter;

use App\Models\NewsletterSubscriber;
use App\Models\Setting;

class ResolveNewsletterMailContextAction
{
    public function execute(string $locale, NewsletterSubscriber $subscriber): array
    {
        $siteProfile = $this->siteProfile();
        $baseUrl = rtrim(config('app.url', 'http://localhost'), '/');
        $brandName = (string) (data_get($siteProfile, 'site_title') ?: config('mail.from.name', config('app.name', 'RadioChi')));
        $responsibleEmail = (string) (data_get($siteProfile, 'admin_email') ?: config('mail.from.address'));

        return [
            'locale' => $locale,
            'brandName' => $brandName,
            'responsibleName' => $brandName,
            'responsibleEmail' => $responsibleEmail,
            'homeUrl' => $locale === 'es' ? $baseUrl.'/' : $baseUrl.'/'.$locale,
            'privacyPolicyUrl' => $locale === 'es'
                ? route('legal.show', ['documentType' => 'privacy'])
                : route('legal.show.localized', ['locale' => $locale, 'documentType' => 'privacy']),
            'unsubscribeUrl' => route('newsletter.unsubscribe', [
                'token' => $subscriber->unsubscribe_token,
                'locale' => $locale,
            ]),
            'unsubscribeMailto' => 'mailto:'.$responsibleEmail.'?subject=unsubscribe',
        ];
    }

    private function siteProfile(): array
    {
        $setting = Setting::query()
            ->where('group', 'general')
            ->where('key', 'site_profile')
            ->first();

        return is_array($setting?->value) ? $setting->value : [];
    }
}
