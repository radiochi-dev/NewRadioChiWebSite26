@component('emails.newsletter.layout', [
    'locale' => $locale,
    'subject' => $campaign->subject,
    'eyebrow' => $context['brandName'],
    'headline' => $campaign->subject,
    'intro' => __('newsletter.mail.campaign.intro', locale: $locale),
    'context' => $context,
])
    <div style="font-size:15px;line-height:1.8;color:#cbd5e1;">
        {!! $campaign->html_body !!}
    </div>

    <p style="margin:24px 0 0;font-size:13px;line-height:1.7;color:#94a3b8;">
        {{ __('newsletter.mail.campaign.notice', locale: $locale) }}
    </p>

    <p style="margin:18px 0 0;font-size:13px;line-height:1.7;color:#cbd5e1;">
        {{ __('newsletter.mail.campaign.unsubscribe_helper', locale: $locale) }}
    </p>

    <p style="margin:14px 0 0;">
        <a
            href="{{ $context['unsubscribeUrl'] }}"
            style="display:inline-block;border-radius:999px;border:1px solid #38bdf8;padding:12px 20px;color:#38bdf8;text-decoration:none;font-size:13px;font-weight:700;"
        >
            {{ __('newsletter.mail.campaign.unsubscribe_cta', locale: $locale) }}
        </a>
    </p>
@endcomponent
