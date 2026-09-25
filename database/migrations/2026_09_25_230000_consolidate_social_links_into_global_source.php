<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('social_links')
            ->orderByRaw("
                case location
                    when 'global' then 0
                    when 'contact' then 1
                    when 'footer' then 2
                    else 3
                end
            ")
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $keptIds = [];
        $position = 1;

        foreach ($rows as $row) {
            $platform = strtolower(trim((string) $row->platform));

            if ($platform === '' || array_key_exists($platform, $keptIds)) {
                continue;
            }

            $keptIds[$platform] = (int) $row->id;

            DB::table('social_links')
                ->where('id', $row->id)
                ->update([
                    'location' => 'global',
                    'position' => $position,
                    'updated_at' => now(),
                ]);

            $position++;
        }

        if ($keptIds !== []) {
            DB::table('social_links')
                ->whereNotIn('id', array_values($keptIds))
                ->delete();
        }

        Schema::table('social_links', function (Blueprint $table): void {
            $table->unique('platform');
        });
    }

    public function down(): void
    {
        Schema::table('social_links', function (Blueprint $table): void {
            $table->dropUnique(['platform']);
        });
    }
};
