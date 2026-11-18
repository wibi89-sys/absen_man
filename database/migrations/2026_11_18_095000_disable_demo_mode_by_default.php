<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('school_settings') && Schema::hasColumn('school_settings', 'enable_demo_mode')) {
            Schema::table('school_settings', function (Blueprint $table) {
                $table->boolean('enable_demo_mode')->default(false)->change();
            });

            DB::table('school_settings')->update(['enable_demo_mode' => false]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('school_settings') && Schema::hasColumn('school_settings', 'enable_demo_mode')) {
            Schema::table('school_settings', function (Blueprint $table) {
                $table->boolean('enable_demo_mode')->default(true)->change();
            });
        }
    }
};
