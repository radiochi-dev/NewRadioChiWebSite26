<?php

namespace Tests\Feature;

use App\Mail\NewsletterDoubleOptIn;
use App\Models\NewsletterSubscriber;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NewsletterPublicFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_subscribe_creates_pending_subscriber_with_legal_trace(): void
    {
        Mail::fake();

        $response = $this->from('/en')->post('/newsletter/subscribe', [
            'locale' => 'en',
            'email' => 'NewUser@GMAIL.COM',
            'privacy_accepted' => '1',
            'website' => '',
        ]);

        $response->assertRedirect('/en');
        $response->assertSessionHas('success');

        $subscriber = NewsletterSubscriber::query()
            ->where('email', 'newuser@gmail.com')
            ->firstOrFail();

        $this->assertFalse($subscriber->is_active);
        $this->assertNull($subscriber->subscribed_at);
        $this->assertNull($subscriber->unsubscribed_at);
        $this->assertNotNull($subscriber->confirmation_token);
        $this->assertSame(64, strlen((string) $subscriber->confirmation_token));
        $this->assertNotNull($subscriber->unsubscribe_token);
        $this->assertSame(64, strlen((string) $subscriber->unsubscribe_token));
        $this->assertSame('127.0.0.0', $subscriber->ip_address);
        $this->assertSame('v1.1', $subscriber->consent_text_version);

        Mail::assertQueued(NewsletterDoubleOptIn::class, function (NewsletterDoubleOptIn $mailable) use ($subscriber): bool {
            return $mailable->hasTo('newuser@gmail.com')
                && $mailable->envelope()->subject === 'Confirm your newsletter subscription'
                && data_get($mailable->headers()->text, 'List-Unsubscribe-Post') === 'List-Unsubscribe=One-Click'
                && str_contains((string) data_get($mailable->headers()->text, 'List-Unsubscribe'), $subscriber->unsubscribe_token);
        });
    }

    public function test_public_subscribe_reuses_unsubscribed_subscriber_with_new_confirmation_token(): void
    {
        Mail::fake();

        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'legacy@gmail.com',
            'is_active' => false,
            'subscribed_at' => now()->subMonth(),
            'unsubscribed_at' => now()->subWeek(),
            'confirmation_token' => str_repeat('a', 64),
            'unsubscribe_token' => str_repeat('b', 64),
            'consent_text_version' => 'v0.9',
        ]);

        $this->post('/newsletter/subscribe', [
            'locale' => 'es',
            'email' => 'LEGACY@GMAIL.COM',
            'privacy_accepted' => '1',
            'website' => '',
        ])->assertRedirect('/');

        $subscriber->refresh();

        $this->assertSame('legacy@gmail.com', $subscriber->email);
        $this->assertFalse($subscriber->is_active);
        $this->assertNull($subscriber->subscribed_at);
        $this->assertNull($subscriber->unsubscribed_at);
        $this->assertNotSame(str_repeat('a', 64), $subscriber->confirmation_token);
        $this->assertSame(str_repeat('b', 64), $subscriber->unsubscribe_token);
        $this->assertSame('v1.1', $subscriber->consent_text_version);

        Mail::assertQueued(NewsletterDoubleOptIn::class, 1);
    }

    public function test_public_subscribe_with_honeypot_returns_neutral_success_without_persisting_or_queueing(): void
    {
        Mail::fake();

        $this->from('/de')->post('/newsletter/subscribe', [
            'locale' => 'de',
            'email' => 'botcase@gmail.com',
            'privacy_accepted' => '1',
            'website' => 'https://spam.invalid',
        ])->assertRedirect('/de')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('newsletter_subscribers', [
            'email' => 'botcase@gmail.com',
        ]);
        Mail::assertNothingQueued();
    }

    public function test_public_subscribe_is_throttled_after_five_attempts_per_minute_with_localized_error(): void
    {
        Mail::fake();

        for ($index = 1; $index <= 5; $index++) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.55', 'HTTP_USER_AGENT' => 'NewsletterThrottleTest/1.0'])
                ->post('/newsletter/subscribe', [
                    'locale' => 'es',
                    'email' => "ratelimit{$index}@gmail.com",
                    'privacy_accepted' => '1',
                    'website' => '',
                ])
                ->assertRedirect('/');
        }

        $this->from('/ca')
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.55', 'HTTP_USER_AGENT' => 'NewsletterThrottleTest/1.0'])
            ->post('/newsletter/subscribe', [
                'locale' => 'ca',
                'email' => 'ratelimit6@gmail.com',
                'privacy_accepted' => '1',
                'website' => '',
            ])
            ->assertRedirect('/ca')
            ->assertInvalid([
                'email' => 'Has enviat massa sollicituds. Espera un moment abans de tornar-ho a provar.',
            ]);
    }

    public function test_public_subscribe_rejects_duplicate_active_subscriber(): void
    {
        Mail::fake();

        NewsletterSubscriber::query()->create([
            'email' => 'repeat@gmail.com',
            'is_active' => true,
            'subscribed_at' => now()->subDay(),
            'unsubscribed_at' => null,
            'confirmation_token' => null,
            'unsubscribe_token' => str_repeat('c', 64),
            'consent_text_version' => 'v1.1',
        ]);

        $this->from('/')->post('/newsletter/subscribe', [
            'locale' => 'es',
            'email' => 'REPEAT@GMAIL.COM',
            'privacy_accepted' => '1',
            'website' => '',
        ])->assertRedirect('/')
            ->assertInvalid(['email']);

        $this->assertSame(1, NewsletterSubscriber::query()->count());
        Mail::assertNothingQueued();
    }

    public function test_signed_confirmation_activates_the_subscriber(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'confirm@gmail.com',
            'is_active' => false,
            'confirmation_token' => str_repeat('d', 64),
            'unsubscribe_token' => str_repeat('e', 64),
            'consent_text_version' => 'v1.1',
        ]);

        $url = URL::temporarySignedRoute('newsletter.confirm', now()->addMinutes(30), [
            'token' => $subscriber->confirmation_token,
            'locale' => 'ca',
        ]);

        $this->get($url)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Newsletter/Status')
                ->where('tone', 'success')
                ->where('locale', 'ca'));

        $subscriber->refresh();

        $this->assertTrue($subscriber->is_active);
        $this->assertNotNull($subscriber->subscribed_at);
        $this->assertNull($subscriber->unsubscribed_at);
        $this->assertNull($subscriber->confirmation_token);
    }

    public function test_confirmation_with_expired_signed_url_renders_error_without_activating_subscriber(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'expired-confirm@gmail.com',
            'is_active' => false,
            'confirmation_token' => str_repeat('x', 64),
            'unsubscribe_token' => str_repeat('y', 64),
            'consent_text_version' => 'v1.1',
        ]);

        $url = URL::temporarySignedRoute('newsletter.confirm', CarbonImmutable::now()->subMinute(), [
            'token' => $subscriber->confirmation_token,
            'locale' => 'de',
        ]);

        $this->get($url)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Newsletter/Status')
                ->where('tone', 'error')
                ->where('locale', 'de'));

        $subscriber->refresh();

        $this->assertFalse($subscriber->is_active);
        $this->assertNull($subscriber->subscribed_at);
        $this->assertSame(str_repeat('x', 64), $subscriber->confirmation_token);
    }

    public function test_confirmation_with_invalid_signature_renders_error_status_page(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'invalid-confirm@gmail.com',
            'is_active' => false,
            'confirmation_token' => str_repeat('f', 64),
            'unsubscribe_token' => str_repeat('g', 64),
            'consent_text_version' => 'v1.1',
        ]);

        $this->get('/newsletter/confirm/'.$subscriber->confirmation_token.'?locale=fr')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Newsletter/Status')
                ->where('tone', 'error')
                ->where('locale', 'fr'));

        $subscriber->refresh();

        $this->assertFalse($subscriber->is_active);
        $this->assertSame(str_repeat('f', 64), $subscriber->confirmation_token);
    }

    public function test_unsubscribe_endpoint_supports_get_and_post_one_click(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'unsubscribe@gmail.com',
            'is_active' => true,
            'subscribed_at' => now()->subDays(2),
            'unsubscribe_token' => str_repeat('h', 64),
            'consent_text_version' => 'v1.1',
        ]);

        $this->get('/newsletter/unsubscribe/'.$subscriber->unsubscribe_token.'?locale=it')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Newsletter/Status')
                ->where('tone', 'success')
                ->where('locale', 'it'));

        $subscriber->refresh();

        $this->assertFalse($subscriber->is_active);
        $this->assertNotNull($subscriber->unsubscribed_at);

        $this->post('/newsletter/unsubscribe/'.$subscriber->unsubscribe_token, [
            'locale' => 'it',
        ])->assertNoContent();

        $subscriber->refresh();

        $this->assertFalse($subscriber->is_active);
        $this->assertNotNull($subscriber->unsubscribed_at);
    }
}
