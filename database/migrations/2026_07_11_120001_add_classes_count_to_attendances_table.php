<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // How many classes this attendance row counts for (1 = single, 2 = double).
            $table->unsignedTinyInteger('classes_count')->default(1)->after('attended_on');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('classes_count');
        });
    }
};
