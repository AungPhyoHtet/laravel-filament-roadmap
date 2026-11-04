<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert plain-text descriptions (from the old Textarea) into HTML paragraphs
     * so the RichEditor can load and edit them.
     */
    public function up(): void
    {
        DB::table('features')
            ->where('description', 'not like', '<%')
            ->orderBy('id')
            ->each(function (object $feature): void {
                $paragraphs = preg_split('/\R{2,}/', trim((string) $feature->description)) ?: [];

                $html = collect($paragraphs)
                    ->filter(fn (string $paragraph): bool => $paragraph !== '')
                    ->map(fn (string $paragraph): string => '<p>'.nl2br(e($paragraph), false).'</p>')
                    ->implode('');

                DB::table('features')
                    ->where('id', $feature->id)
                    ->update(['description' => $html !== '' ? $html : '<p></p>']);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
