<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // How many months the course runs (e.g. 1, 5). Nullable = unspecified.
            $table->unsignedInteger('duration_months')->nullable()->after('badge_en');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('duration_months');
        });
    }
};
