<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artisan_business_cards', function (Blueprint $table) {
            $table->json('portfolio_paths')->nullable()->after('portfolio_path');
        });

        $rows = DB::table('artisan_business_cards')
            ->whereNotNull('portfolio_path')
            ->where('portfolio_path', '!=', '')
            ->get(['id', 'portfolio_path']);
        foreach ($rows as $row) {
            DB::table('artisan_business_cards')
                ->where('id', $row->id)
                ->update([
                    'portfolio_paths' => json_encode([$row->portfolio_path]),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('artisan_business_cards', function (Blueprint $table) {
            $table->dropColumn('portfolio_paths');
        });
    }
};
