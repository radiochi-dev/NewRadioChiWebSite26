<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use App\Models\LegalDocumentTranslation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicLegalDocumentController extends Controller
{
    private const LOCALES = ['es', 'en', 'ca', 'fr', 'it', 'de'];

    public function show(Request $request, string $documentType): Response
    {
        return $this->renderDocument($request, 'es', $documentType);
    }

    public function showLocalized(Request $request, string $locale, string $documentType): Response
    {
        return $this->renderDocument($request, $locale, $documentType);
    }

    private function renderDocument(Request $request, string $locale, string $documentType): Response
    {
        $resolvedLocale = in_array($locale, self::LOCALES, true) ? $locale : 'es';

        app()->setLocale($resolvedLocale);

        $document = LegalDocument::query()
            ->where('document_type', $documentType)
            ->where('is_published', true)
            ->with('translations')
            ->firstOrFail();

        /** @var LegalDocumentTranslation|null $translation */
        $translation = $document->translations->firstWhere('locale', $resolvedLocale)
            ?? $document->translations->firstWhere('locale', 'es')
            ?? $document->translations->first();

        return Inertia::render('Legal/Show', [
            'locale' => $resolvedLocale,
            'title' => $translation?->title ?? ucfirst($documentType),
            'summary' => $translation?->summary,
            'content' => $translation?->content,
            'documentType' => $documentType,
            'currentPath' => $request->getPathInfo(),
        ]);
    }
}
