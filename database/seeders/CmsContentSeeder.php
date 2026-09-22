<?php

namespace Database\Seeders;

use App\Actions\Legacy\ImportLegacyContentAction;
use Illuminate\Database\Seeder;

class CmsContentSeeder extends Seeder
{
    public function run(): void
    {
        app(ImportLegacyContentAction::class)->import();
    }
}
