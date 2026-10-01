@component('emails.newsletter.layout', [
    'locale' => $locale,
    'subject' => __('newsletter.mail.double_opt_in.subject', locale: $locale),
    'eyebrow' => $context['brandName'],
    'headline' => __('newsletter.mail.double_opt_in.headline', locale: $locale),
    'intro' => __('newsletter.mail.double_opt_in.intro', locale: $locale),
    'context' => $context,
])
    <p style="margin:0 0 24px;font-size:15px;line-height:1.8;color:#cbd5e1;">
        {{ __('newsletter.mail.double_opt_in.body', ['email' => $subscriber->email], locale: $locale) }}
    </p>

    <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 24px;">
        <tr>
            <td>
                <a href="{{ $confirmUrl }}" style="display:inline-block;border-radius:999px;background:#0ea5e9;padding:14px 28px;font-size:14px;font-weight:700;color:#f8fafc;text-decoration:none;">
                    {{ __('newsletter.mail.double_opt_in.cta', locale: $locale) }}
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 16px;font-size:13px;line-height:1.7;color:#94a3b8;">
        {{ __('newsletter.mail.double_opt_in.expiry', locale: $locale) }}
    </p>

    <p style="margin:0;font-size:13px;line-height:1.7;color:#94a3b8;">
        {{ __('newsletter.mail.double_opt_in.ignore', locale: $locale) }}
    </p>
@endcomponent
