<?php

namespace App\Http\Requests\Backoffice;

use App\Support\BackofficeLocales;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class BackofficePageTranslationUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageBackofficeContent() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'content' => ['nullable', 'array'],
        ];

        if (! $this->route('translation')) {
            $rules['locale'] = ['required', 'string', 'max:5', Rule::in(BackofficeLocales::values())];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('content') && is_string($this->input('content'))) {
            $this->merge([
                'content' => $this->decodeJsonField('content'),
            ]);

            return;
        }

        $typedContent = $this->typedContentPayload();

        if ($typedContent !== []) {
            $this->merge([
                'content' => $typedContent,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function typedContentPayload(): array
    {
        $mapping = [
            'home_name' => 'name',
            'home_title' => 'title',
            'home_satisfied_customers' => 'satisfied_customers',
            'home_watch_resume' => 'watch_resume',
            'music_title' => 'title',
            'music_subtitle' => 'subtitle',
            'music_description' => 'description',
            'calendar_title' => 'title',
            'calendar_buy_tickets' => 'buyTickets',
            'calendar_view_all' => 'viewAllEvents',
            'calendar_no_events' => 'noEvents',
            'media_title' => 'title',
            'media_photos' => 'photos',
            'media_videos' => 'videos',
            'media_photo_label' => 'photoLabel',
            'media_video_label' => 'videoLabel',
            'media_youtube_cta' => 'viewMoreOnYoutube',
            'contact_title' => 'title',
            'contact_subtitle' => 'subtitle',
            'contact_email_label' => 'email_label',
            'contact_phone_label' => 'phone_label',
            'contact_get_direction' => 'get_direction',
            'contact_form_title' => 'form.title',
            'contact_submit_button' => 'form.submit_button',
            'contact_success_message' => 'form.success_message',
            'contact_error_message' => 'form.error_message',
        ];

        $hasTypedInput = collect(array_keys($mapping))
            ->contains(fn (string $field): bool => $this->has($field));

        if (! $hasTypedInput) {
            return [];
        }

        $base = $this->route('translation')?->content;

        if (! is_array($base)) {
            $base = [];
        }

        foreach ($mapping as $field => $key) {
            if (! $this->has($field)) {
                continue;
            }

            if (str_contains($key, '.')) {
                $base[$key] = $this->input($field);
                continue;
            }

            Arr::set($base, $key, $this->input($field));
        }

        return $base;
    }

    private function decodeJsonField(string $field): ?array
    {
        $value = $this->input($field);

        if ($value === null || $value === '') {
            return null;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : null;
    }
}
