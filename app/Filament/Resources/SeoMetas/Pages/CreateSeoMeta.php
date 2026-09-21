<?php

namespace App\Filament\Resources\SeoMetas\Pages;

use App\Filament\Resources\SeoMetas\SeoMetaResource;
use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;

class CreateSeoMeta extends CreateRecord
{
    protected static string $resource = SeoMetaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return SeoMeta::query()->updateOrCreate(
            [
                'entity_type' => $data['entity_type'],
                'entity_id' => $data['entity_id'],
                'locale' => $data['locale'],
            ],
            [
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'canonical_url' => $data['canonical_url'] ?? null,
                'open_graph' => $data['open_graph'] ?? null,
                'twitter_card' => $data['twitter_card'] ?? null,
                'json_ld' => $data['json_ld'] ?? null,
            ],
        );
    }
}
