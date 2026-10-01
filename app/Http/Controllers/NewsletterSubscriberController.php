<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Support\NewsletterLegalConsent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewsletterSubscriberController extends Controller
{
    private const LOCALES = ['es', 'en', 'ca', 'fr', 'it', 'de'];

    public function store(Request $request): RedirectResponse
    {
        $locale = $this->resolveLocale($request);
        $this->activateLocale($locale);

        if (filled($request->input('website'))) {
            return $this->redirectToHome($locale)->with('success', __('newsletter.subscribe.success'));
        }

        $validated = Validator::make(
            [
                'email' => Str::lower(trim((string) $request->input('email'))),
                'privacy_accepted' => $request->input('privacy_accepted'),
            ],
            [
                'email' => ['required', 'email:rfc,dns', 'max:255'],
                'privacy_accepted' => ['accepted'],
            ],
            [
                'email.required' => __('newsletter.validation.email_required'),
                'email.email' => __('newsletter.validation.email_invalid'),
                'email.max' => __('newsletter.validation.email_invalid'),
                'privacy_accepted.accepted' => __('newsletter.validation.privacy_accepted'),
            ],
        )->validate();

        $subscriber = NewsletterSubscriber::query()
            ->where('email', $validated['email'])
            ->first();

        if ($subscriber instanceof NewsletterSubscriber && $subscriber->is_active && $subscriber->unsubscribed_at === null) {
            throw ValidationException::withMessages([
                'email' => __('newsletter.validation.already_subscribed'),
            ]);
        }

        $attributes = [
            'email' => $validated['email'],
            'is_active' => true,
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
            'confirmation_token' => null,
            'ip_address' => $this->anonymizeIpAddress($request->ip()),
            'user_agent' => Str::limit((string) $request->userAgent(), 65535, ''),
            'consent_text_version' => NewsletterLegalConsent::consentVersion(),
        ];

        if ($subscriber instanceof NewsletterSubscriber) {
            $subscriber->fill($attributes)->save();
        } else {
            $subscriber = NewsletterSubscriber::query()->create($attributes);
        }

        return $this->redirectToHome($locale)->with('success', __('newsletter.subscribe.success'));
    }

    public function confirm(Request $request, string $token): Response
    {
        $locale = $this->resolveLocale($request);
        $this->activateLocale($locale);

        if (! $request->hasValidSignature()) {
            return $this->statusPage($request, $locale, 'error', 'confirmation_invalid');
        }

        $subscriber = NewsletterSubscriber::query()
            ->where('confirmation_token', $token)
            ->first();

        if (! $subscriber instanceof NewsletterSubscriber) {
            return $this->statusPage($request, $locale, 'error', 'confirmation_invalid');
        }

        $subscriber->forceFill([
            'is_active' => true,
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
            'confirmation_token' => null,
        ])->save();

        return $this->statusPage($request, $locale, 'success', 'confirmed');
    }

    public function unsubscribe(Request $request, string $token): Response|\Illuminate\Http\Response
    {
        $locale = $this->resolveLocale($request);
        $this->activateLocale($locale);

        $subscriber = NewsletterSubscriber::query()
            ->where('unsubscribe_token', $token)
            ->first();

        if (! $subscriber instanceof NewsletterSubscriber) {
            if ($request->isMethod('post')) {
                return response()->noContent();
            }

            return $this->statusPage($request, $locale, 'error', 'unsubscribe_invalid');
        }

        if ($subscriber->is_active || $subscriber->unsubscribed_at === null) {
            $subscriber->forceFill([
                'is_active' => false,
                'unsubscribed_at' => now(),
                'confirmation_token' => null,
            ])->save();
        }

        if ($request->isMethod('post')) {
            return response()->noContent();
        }

        return $this->statusPage($request, $locale, 'success', 'unsubscribed');
    }

    private function statusPage(Request $request, string $locale, string $tone, string $messageKey): Response
    {
        return Inertia::render('Newsletter/Status', [
            'locale' => $locale,
            'tone' => $tone,
            'title' => __('newsletter.status.'.$messageKey.'.title'),
            'message' => __('newsletter.status.'.$messageKey.'.message'),
            'primaryAction' => [
                'label' => __('newsletter.actions.back_home'),
                'href' => $this->homePath($locale),
            ],
            'currentPath' => $request->getPathInfo(),
        ]);
    }

    private function resolveLocale(Request $request): string
    {
        $candidate = $request->input('locale')
            ?? $request->query('locale')
            ?? $request->cookie('radiochi_locale')
            ?? 'es';

        return in_array($candidate, self::LOCALES, true) ? $candidate : 'es';
    }

    private function activateLocale(string $locale): void
    {
        app()->setLocale($locale);
        Cookie::queue(Cookie::forever('radiochi_locale', $locale));
    }

    private function redirectToHome(string $locale): RedirectResponse
    {
        return redirect()->to($this->homePath($locale));
    }

    private function homePath(string $locale): string
    {
        return $locale === 'es' ? '/' : '/'.$locale;
    }

    private function anonymizeIpAddress(?string $ipAddress): ?string
    {
        if (! is_string($ipAddress) || $ipAddress === '') {
            return null;
        }

        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $segments = explode('.', $ipAddress);
            $segments[3] = '0';

            return implode('.', $segments);
        }

        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $segments = explode(':', $ipAddress);

            return implode(':', array_pad(array_slice($segments, 0, 4), 8, '0'));
        }

        return null;
    }
}
