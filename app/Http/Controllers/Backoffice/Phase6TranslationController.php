<?php

namespace App\Http\Controllers\Backoffice;

use App\Actions\Backoffice\BuildBackofficePhase6CrudPayloadAction;
use App\Actions\Backoffice\SaveBackofficePhase6ModuleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\BackofficeLegalDocumentTranslationUpsertRequest;
use App\Http\Requests\Backoffice\BackofficeMusicTrackTranslationUpsertRequest;
use App\Http\Requests\Backoffice\BackofficePageBlockTranslationUpsertRequest;
use App\Http\Requests\Backoffice\BackofficePageTranslationUpsertRequest;
use App\Http\Requests\Backoffice\BackofficeSettingTranslationUpsertRequest;
use App\Models\LegalDocument;
use App\Models\LegalDocumentTranslation;
use App\Models\MusicTrack;
use App\Models\MusicTrackTranslation;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\PageBlockTranslation;
use App\Models\PageTranslation;
use App\Models\Setting;
use App\Models\SettingTranslation;
use App\Models\User;
use App\Support\Backoffice\BackofficePath;
use App\Support\BackofficeLocales;
use Inertia\Inertia;
use Inertia\Response;

class Phase6TranslationController extends Controller
{
    public function __construct(
        private readonly BuildBackofficePhase6CrudPayloadAction $payload,
        private readonly SaveBackofficePhase6ModuleAction $save,
    ) {
    }

    public function createPage(Page $page): Response
    {
        /** @var User $user */
        $user = request()->user();

        abort_unless($user->canManageBackofficeContent(), 403);

        return Inertia::render('Backoffice/Preview/ModuleForm', $this->payload->pageTranslationForm(
            $user,
            $page,
            null,
            request()->session()->getOldInput(),
        ));
    }

    public function editPage(Page $page, PageTranslation $translation): Response
    {
        abort_unless($translation->page_id === $page->id, 404);

        /** @var User $user */
        $user = request()->user();

        abort_unless($user->canManageBackofficeContent(), 403);

        return Inertia::render('Backoffice/Preview/ModuleForm', $this->payload->pageTranslationForm(
            $user,
            $page,
            $translation,
            request()->session()->getOldInput(),
        ));
    }

    public function editPageComponent(Page $page, string $component): Response
    {
        /** @var User $user */
        $user = request()->user();

        abort_unless($user->canManageBackofficeContent(), 403);

        $pageBlock = $page->blocks()
            ->with(['page', 'translations'])
            ->where('key', $component)
            ->firstOrFail();

        $locale = request()->query('locale');
        $translation = is_string($locale) && $locale !== ''
            ? $pageBlock->translations->firstWhere('locale', $locale)
            : null;

        abort_if($translation !== null && ! $translation instanceof PageBlockTranslation, 404);

        return Inertia::render('Backoffice/Preview/ModuleForm', $this->payload->pageBlockTranslationForm(
            $user,
            $pageBlock,
            $translation,
            request()->session()->getOldInput(),
        ));
    }

    public function upsertPageComponent(BackofficePageBlockTranslationUpsertRequest $request, Page $page, string $component)
    {
        $pageBlock = $page->blocks()
            ->with(['page', 'translations'])
            ->where('key', $component)
            ->firstOrFail();

        $data = $request->validated();
        $locale = is_string($data['locale'] ?? null) && $data['locale'] !== ''
            ? $data['locale']
            : (is_string($request->query('locale')) ? $request->query('locale') : 'es');

        /** @var PageBlockTranslation|null $translation */
        $translation = $pageBlock->translations->firstWhere('locale', $locale);

        $savedTranslation = $this->save->savePageBlockTranslation($pageBlock, $data, $translation);

        return redirect($this->editorialReturnPath(
            from: $request->query('from'),
            locale: $savedTranslation->locale,
            fallback: $this->pageComponentEditPath($pageBlock, $savedTranslation->locale),
        ))
            ->with('success', $translation instanceof PageBlockTranslation
                ? 'Traduccion de bloque actualizada correctamente.'
                : 'Traduccion de bloque guardada correctamente.');
    }

    public function storePage(BackofficePageTranslationUpsertRequest $request, Page $page)
    {
        $this->save->savePageTranslation($page, $request->validated());

        return redirect($this->editorialReturnPath(
            from: $request->query('from'),
            locale: $request->validated('locale'),
            fallback: BackofficePath::active('pages/'.$page->slug.'/edit'),
        ))
            ->with('success', 'Traduccion de pagina guardada correctamente.');
    }

    public function updatePage(BackofficePageTranslationUpsertRequest $request, Page $page, PageTranslation $translation)
    {
        abort_unless($translation->page_id === $page->id, 404);

        $this->save->savePageTranslation($page, $request->validated(), $translation);

        return redirect($this->editorialReturnPath(
            from: $request->query('from'),
            locale: $translation->locale,
            fallback: BackofficePath::active('pages/'.$page->slug.'/edit'),
        ))
            ->with('success', 'Traduccion de pagina actualizada correctamente.');
    }

    public function createLegalDocument(LegalDocument $legalDocument): Response
    {
        /** @var User $user */
        $user = request()->user();

        abort_unless($user->canManageBackofficeContent(), 403);

        return Inertia::render('Backoffice/Preview/ModuleForm', $this->payload->legalDocumentTranslationForm(
            $user,
            $legalDocument,
            null,
            request()->session()->getOldInput(),
        ));
    }

    public function editLegalDocument(LegalDocument $legalDocument, LegalDocumentTranslation $translation): Response
    {
        abort_unless($translation->legal_document_id === $legalDocument->id, 404);

        /** @var User $user */
        $user = request()->user();

        abort_unless($user->canManageBackofficeContent(), 403);

        return Inertia::render('Backoffice/Preview/ModuleForm', $this->payload->legalDocumentTranslationForm(
            $user,
            $legalDocument,
            $translation,
            request()->session()->getOldInput(),
        ));
    }

    public function storeLegalDocument(BackofficeLegalDocumentTranslationUpsertRequest $request, LegalDocument $legalDocument)
    {
        $this->save->saveLegalDocumentTranslation($legalDocument, $request->validated());

        return redirect($this->editorialReturnPath(
            from: $request->query('from'),
            locale: $request->validated('locale'),
            fallback: BackofficePath::active('legal-documents/'.$legalDocument->getKey().'/edit'),
        ))
            ->with('success', 'Traduccion de documento legal guardada correctamente.');
    }

    public function updateLegalDocument(BackofficeLegalDocumentTranslationUpsertRequest $request, LegalDocument $legalDocument, LegalDocumentTranslation $translation)
    {
        abort_unless($translation->legal_document_id === $legalDocument->id, 404);

        $this->save->saveLegalDocumentTranslation($legalDocument, $request->validated(), $translation);

        return redirect($this->editorialReturnPath(
            from: $request->query('from'),
            locale: $translation->locale,
            fallback: BackofficePath::active('legal-documents/'.$legalDocument->getKey().'/edit'),
        ))
            ->with('success', 'Traduccion de documento legal actualizada correctamente.');
    }

    public function createMusicTrack(MusicTrack $musicTrack): Response
    {
        /** @var User $user */
        $user = request()->user();

        abort_unless($user->canManageBackofficeContent(), 403);

        return Inertia::render('Backoffice/Preview/ModuleForm', $this->payload->musicTrackTranslationForm(
            $user,
            $musicTrack,
            null,
            request()->session()->getOldInput(),
        ));
    }

    public function editMusicTrack(MusicTrack $musicTrack, MusicTrackTranslation $translation): Response
    {
        abort_unless($translation->music_track_id === $musicTrack->id, 404);

        /** @var User $user */
        $user = request()->user();

        abort_unless($user->canManageBackofficeContent(), 403);

        return Inertia::render('Backoffice/Preview/ModuleForm', $this->payload->musicTrackTranslationForm(
            $user,
            $musicTrack,
            $translation,
            request()->session()->getOldInput(),
        ));
    }

    public function storeMusicTrack(BackofficeMusicTrackTranslationUpsertRequest $request, MusicTrack $musicTrack)
    {
        $this->save->saveMusicTrackTranslation($musicTrack, $request->validated());

        return redirect(BackofficePath::active('music-tracks/'.$musicTrack->getKey().'/edit'))
            ->with('success', 'Traduccion de track musical guardada correctamente.');
    }

    public function updateMusicTrack(BackofficeMusicTrackTranslationUpsertRequest $request, MusicTrack $musicTrack, MusicTrackTranslation $translation)
    {
        abort_unless($translation->music_track_id === $musicTrack->id, 404);

        $this->save->saveMusicTrackTranslation($musicTrack, $request->validated(), $translation);

        return redirect(BackofficePath::active('music-tracks/'.$musicTrack->getKey().'/edit'))
            ->with('success', 'Traduccion de track musical actualizada correctamente.');
    }

    public function createPageBlock(PageBlock $pageBlock): Response
    {
        /** @var User $user */
        $user = request()->user();

        abort_unless($user->canManageBackofficeContent(), 403);

        return Inertia::render('Backoffice/Preview/ModuleForm', $this->payload->pageBlockTranslationForm(
            $user,
            $pageBlock->load('page'),
            null,
            request()->session()->getOldInput(),
        ));
    }

    public function editPageBlock(PageBlock $pageBlock, PageBlockTranslation $translation): Response
    {
        abort_unless($translation->page_block_id === $pageBlock->id, 404);

        /** @var User $user */
        $user = request()->user();

        abort_unless($user->canManageBackofficeContent(), 403);

        return Inertia::render('Backoffice/Preview/ModuleForm', $this->payload->pageBlockTranslationForm(
            $user,
            $pageBlock->load('page'),
            $translation,
            request()->session()->getOldInput(),
        ));
    }

    public function storePageBlock(BackofficePageBlockTranslationUpsertRequest $request, PageBlock $pageBlock)
    {
        $this->save->savePageBlockTranslation($pageBlock, $request->validated());

        return redirect($this->editorialReturnPath(
            from: $request->query('from'),
            locale: $request->validated('locale'),
            fallback: $this->pageComponentEditPath($pageBlock, $request->validated('locale')),
        ))
            ->with('success', 'Traduccion de bloque guardada correctamente.');
    }

    public function updatePageBlock(BackofficePageBlockTranslationUpsertRequest $request, PageBlock $pageBlock, PageBlockTranslation $translation)
    {
        abort_unless($translation->page_block_id === $pageBlock->id, 404);

        $this->save->savePageBlockTranslation($pageBlock, $request->validated(), $translation);

        return redirect($this->editorialReturnPath(
            from: $request->query('from'),
            locale: $translation->locale,
            fallback: $this->pageComponentEditPath($pageBlock, $translation->locale),
        ))
            ->with('success', 'Traduccion de bloque actualizada correctamente.');
    }

    public function createSetting(Setting $setting): Response
    {
        /** @var User $user */
        $user = request()->user();

        abort_unless($user->canManageBackofficeContent(), 403);

        return Inertia::render('Backoffice/Preview/ModuleForm', $this->payload->settingTranslationForm(
            $user,
            $setting,
            null,
            request()->session()->getOldInput(),
        ));
    }

    public function editSetting(Setting $setting, SettingTranslation $translation): Response
    {
        abort_unless($translation->setting_id === $setting->id, 404);

        /** @var User $user */
        $user = request()->user();

        abort_unless($user->canManageBackofficeContent(), 403);

        return Inertia::render('Backoffice/Preview/ModuleForm', $this->payload->settingTranslationForm(
            $user,
            $setting,
            $translation,
            request()->session()->getOldInput(),
        ));
    }

    public function storeSetting(BackofficeSettingTranslationUpsertRequest $request, Setting $setting)
    {
        $this->save->saveSettingTranslation($setting, $request->validated());

        return redirect($this->editorialReturnPath(
            from: $request->query('from'),
            locale: $request->validated('locale'),
            fallback: BackofficePath::active('settings/'.$setting->getKey().'/edit'),
        ))
            ->with('success', 'Traduccion de setting guardada correctamente.');
    }

    public function updateSetting(BackofficeSettingTranslationUpsertRequest $request, Setting $setting, SettingTranslation $translation)
    {
        abort_unless($translation->setting_id === $setting->id, 404);

        $this->save->saveSettingTranslation($setting, $request->validated(), $translation);

        return redirect($this->editorialReturnPath(
            from: $request->query('from'),
            locale: $translation->locale,
            fallback: BackofficePath::active('settings/'.$setting->getKey().'/edit'),
        ))
            ->with('success', 'Traduccion de setting actualizada correctamente.');
    }

    private function editorialReturnPath(mixed $from, ?string $locale, string $fallback): string
    {
        if (! is_string($from) || trim($from) === '') {
            return $fallback;
        }

        $activeLocale = is_string($locale) && in_array($locale, BackofficeLocales::values(), true)
            ? $locale
            : 'es';

        if ($from !== 'login') {
            $page = Page::query()
                ->when(
                    ctype_digit($from),
                    fn ($query) => $query->whereKey($from),
                    fn ($query) => $query->where('slug', $from),
                )
                ->first();

            if ($page instanceof Page) {
                $query = array_filter([
                    'locale' => $activeLocale,
                    'focus' => request()->query('focus'),
                ], fn (mixed $value): bool => is_string($value) && $value !== '');

                return BackofficePath::active('pages/'.$page->slug.'/edit').'?'.http_build_query($query);
            }
        }

        if ($from !== 'login') {
            return $fallback;
        }

        $query = array_filter([
            'locale' => $activeLocale,
            'focus' => request()->query('focus'),
        ], fn (mixed $value): bool => is_string($value) && $value !== '');

        return BackofficePath::active('pages/login/edit').'?'.http_build_query($query);
    }

    private function pageComponentEditPath(PageBlock $pageBlock, ?string $locale): string
    {
        if (! $pageBlock->page instanceof Page) {
            return BackofficePath::active('page-blocks/'.$pageBlock->getKey().'/edit');
        }

        $activeLocale = is_string($locale) && in_array($locale, BackofficeLocales::values(), true)
            ? $locale
            : 'es';

        return BackofficePath::active('pages/'.$pageBlock->page->slug.'/components/'.$pageBlock->key.'/edit')
            .'?'.http_build_query(['locale' => $activeLocale]);
    }
}
