<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageBlock;
use App\Models\PageBlockTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase4PageBlocksSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_4_2_adds_page_blocks_tables_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('page_blocks'));
        $this->assertTrue(Schema::hasTable('page_block_translations'));

        $this->assertTrue(Schema::hasColumns('page_blocks', [
            'page_id',
            'key',
            'type',
            'position',
            'is_active',
            'settings',
        ]));

        $this->assertTrue(Schema::hasColumns('page_block_translations', [
            'page_block_id',
            'locale',
            'content',
        ]));
    }

    public function test_phase_4_2_models_stay_aligned_with_page_blocks_schema(): void
    {
        $this->assertSame('page_blocks', (new PageBlock())->getTable());
        $this->assertSame('page_block_translations', (new PageBlockTranslation())->getTable());
    }

    public function test_phase_4_2_page_blocks_can_be_related_to_pages_and_translations(): void
    {
        $page = Page::query()->create([
            'slug' => 'home',
            'template' => 'home',
        ]);

        $block = $page->blocks()->create([
            'key' => 'hero-main',
            'type' => 'hero',
            'position' => 1,
            'is_active' => true,
            'settings' => [
                'variant' => 'full-screen',
            ],
        ]);

        $translation = $block->translations()->create([
            'locale' => 'es',
            'content' => [
                'headline' => 'RadioChi',
                'cta' => 'Escuchar',
            ],
        ]);

        $this->assertSame($page->id, $block->page->id);
        $this->assertSame($block->id, $translation->pageBlock->id);
        $this->assertSame('hero-main', $page->blocks()->firstOrFail()->key);
        $this->assertSame('RadioChi', $block->translations()->firstOrFail()->content['headline']);
    }
}
