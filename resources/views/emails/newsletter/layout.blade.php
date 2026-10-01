<!DOCTYPE html>
<html lang="{{ $locale ?? 'es' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? config('app.name', 'RadioChi') }}</title>
</head>
<body style="margin:0;padding:0;background:#020617;color:#e2e8f0;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#020617;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#071226;border:1px solid rgba(148,163,184,0.18);border-radius:24px;overflow:hidden;">
                    <tr>
                        <td style="padding:32px 32px 20px;">
                            <p style="margin:0 0 12px;font-size:12px;letter-spacing:0.28em;text-transform:uppercase;color:#38bdf8;">
                                {{ $eyebrow ?? config('app.name', 'RadioChi') }}
                            </p>
                            <h1 style="margin:0;font-size:32px;line-height:1.2;color:#f8fafc;">
                                {{ $headline ?? config('app.name', 'RadioChi') }}
                            </h1>
                            @if (! empty($intro))
                                <p style="margin:18px 0 0;font-size:16px;line-height:1.7;color:#cbd5e1;">
                                    {{ $intro }}
                                </p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 24px;">
                            {{ $slot }}
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 32px 32px;border-top:1px solid rgba(148,163,184,0.16);background:#030712;">
                            <p style="margin:0 0 8px;font-size:13px;line-height:1.7;color:#cbd5e1;">
                                <strong>{{ __('newsletter.mail.footer.responsible_label', locale: $locale ?? 'es') }}:</strong>
                                {{ $context['responsibleName'] ?? config('app.name', 'RadioChi') }}
                            </p>
                            <p style="margin:0 0 8px;font-size:13px;line-height:1.7;color:#cbd5e1;">
                                <strong>{{ __('newsletter.mail.footer.contact_label', locale: $locale ?? 'es') }}:</strong>
                                <a href="mailto:{{ $context['responsibleEmail'] ?? config('mail.from.address') }}" style="color:#38bdf8;text-decoration:none;">
                                    {{ $context['responsibleEmail'] ?? config('mail.from.address') }}
                                </a>
                            </p>
                            <p style="margin:0 0 8px;font-size:13px;line-height:1.7;color:#cbd5e1;">
                                <strong>{{ __('newsletter.mail.footer.purpose_label', locale: $locale ?? 'es') }}:</strong>
                                {{ __('newsletter.mail.footer.purpose_value', locale: $locale ?? 'es') }}
                            </p>
                            <p style="margin:0 0 8px;font-size:13px;line-height:1.7;color:#cbd5e1;">
                                <a href="{{ $context['privacyPolicyUrl'] }}" style="color:#38bdf8;text-decoration:none;">
                                    {{ __('newsletter.mail.footer.privacy_label', locale: $locale ?? 'es') }}
                                </a>
                            </p>
                            <p style="margin:0;font-size:13px;line-height:1.7;color:#cbd5e1;">
                                <a href="{{ $context['unsubscribeUrl'] }}" style="color:#38bdf8;text-decoration:none;">
                                    {{ __('newsletter.mail.footer.unsubscribe_label', locale: $locale ?? 'es') }}
                                </a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
