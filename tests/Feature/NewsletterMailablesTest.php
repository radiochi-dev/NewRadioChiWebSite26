<?php

namespace Tests\Feature;

use App\Jobs\SendNewsletterCampaignJob;
use App\Mail\NewsletterCampaignMail;
use App\Mail\NewsletterDoubleOptIn;
use App\Models\NewsletterCampaign;
use App\Models\LegalDocument;
use App\Models\NewsletterLog;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NewsletterMailablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_double_opt_in_mailable_renders_legal_footer_and_headers(): void
    {
        Setting::query()->updateOrCreate(
            [
                'group' => 'general',
                'key' => 'site_profile',
            ],
            [
                'type' => 'json',
                'value' => [
                    'site_title' => 'RadioChi Legal',
                    'admin_email' => 'legal@radiochi.test',
                ],
                'is_translatable' => false,
                'is_public' => false,
                'position' => 1,
            ],
        );

        $document = LegalDocument::query()->create([
            'slug' => 'privacy-policy',
            'document_type' => 'privacy',
            'version' => '2026.09',
            'position' => 1,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $document->translations()->create([
            'locale' => 'es',
            'title' => 'Politica de Privacidad',
            'summary' => 'Resumen legal',
            'content' => '<p>Contenido legal</p>',
            'cta_label' => 'Politica de Privacidad',
        ]);

        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'mailtest@gmail.com',
            'confirmation_token' => str_repeat('a', 64),
            'unsubscribe_token' => str_repeat('b', 64),
            'consent_text_version' => 'v1.2',
        ]);

        $mailable = new NewsletterDoubleOptIn($subscriber, 'es');

        $mailable->assertHasSubject('Confirma tu suscripcion a la newsletter')
            ->assertSeeInHtml('Confirma tu suscripcion')
            ->assertSeeInHtml('RadioChi Legal')
            ->assertSeeInHtml('legal@radiochi.test')
            ->assertSeeInHtml('Gestionar la suscripcion')
            ->assertSeeInHtml('Consultar la politica de privacidad')
            ->assertSeeInHtml('Darse de baja de la newsletter');

        $this->assertSame('List-Unsubscribe=One-Click', data_get($mailable->headers()->text, 'List-Unsubscribe-Post'));
        $this->assertStringContainsString($subscriber->unsubscribe_token, (string) data_get($mailable->headers()->text, 'List-Unsubscribe'));
        $this->assertStringContainsString('mailto:legal@radiochi.test', (string) data_get($mailable->headers()->text, 'List-Unsubscribe'));
    }

    public function test_public_privacy_policy_page_is_available_for_mail_footer_links(): void
    {
        $document = LegalDocument::query()->create([
            'slug' => 'privacy-policy',
            'document_type' => 'privacy',
            'version' => '2026.09',
            'position' => 1,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $document->translations()->create([
            'locale' => 'en',
            'title' => 'Privacy Policy',
            'summary' => 'Summary',
            'content' => '<p>Privacy body</p>',
            'cta_label' => 'Privacy Policy',
        ]);

        $this->get('/en/legal/privacy')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Legal/Show')
                ->where('title', 'Privacy Policy')
                ->where('locale', 'en'));
    }

    public function test_campaign_job_sends_campaign_mailable_and_logs_delivery(): void
    {
        Mail::fake();

        $campaign = NewsletterCampaign::query()->create([
            'name' => 'Autumn Update',
            'subject' => 'Autumn Update',
            'html_body' => '<p>Body from campaign</p>',
            'status' => 'queued',
        ]);

        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'subscriber@gmail.com',
            'is_active' => true,
            'subscribed_at' => now()->subDay(),
            'unsubscribe_token' => str_repeat('c', 64),
            'consent_text_version' => 'v1.2',
        ]);

        (new SendNewsletterCampaignJob($campaign->id))->handle();

        Mail::assertSent(NewsletterCampaignMail::class, function (NewsletterCampaignMail $mailable) use ($subscriber): bool {
            return $mailable->hasTo('subscriber@gmail.com')
                && $mailable->envelope()->subject === 'Autumn Update'
                && data_get($mailable->headers()->text, 'List-Unsubscribe-Post') === 'List-Unsubscribe=One-Click'
                && str_contains((string) data_get($mailable->headers()->text, 'List-Unsubscribe'), $subscriber->unsubscribe_token);
        });

        $campaign->refresh();

        $this->assertSame('sent', $campaign->status);
        $this->assertSame(1, $campaign->sent_count);
        $this->assertNotNull($campaign->sent_at);
        $this->assertDatabaseHas('newsletter_logs', [
            'campaign_id' => $campaign->id,
            'subscriber_id' => $subscriber->id,
            'status' => 'sent',
        ]);
        $this->assertSame(1, NewsletterLog::query()->count());
    }

    public function test_campaign_mailable_renders_visible_unsubscribe_cta_to_public_page(): void
    {
        $campaign = NewsletterCampaign::query()->create([
            'name' => 'Visible Unsubscribe',
            'subject' => 'Visible Unsubscribe',
            'html_body' => '<p>Campaign body</p>',
            'status' => 'queued',
        ]);

        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'visible-unsubscribe@gmail.com',
            'is_active' => true,
            'subscribed_at' => now()->subDay(),
            'unsubscribe_token' => str_repeat('u', 64),
            'consent_text_version' => 'v1.2',
        ]);

        $mailable = new NewsletterCampaignMail($campaign, $subscriber, 'en');

        $mailable->assertSeeInHtml('If you no longer want to receive future campaigns')
            ->assertSeeInHtml('Unsubscribe from the newsletter')
            ->assertSeeInHtml('/newsletter/unsubscribe/'.$subscriber->unsubscribe_token);
    }
}

