<?php

namespace App\Support;

class NewsletterLegalConsent
{
    public const CONSENT_VERSION = 'v1.1';

    public const LEGAL_DOCUMENT_VERSION = '2026.09';

    public static function consentVersion(): string
    {
        return self::CONSENT_VERSION;
    }

    public static function legalDocumentVersion(): string
    {
        return self::LEGAL_DOCUMENT_VERSION;
    }
}
