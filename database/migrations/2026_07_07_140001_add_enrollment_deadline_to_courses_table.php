<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Last day the course is advertised / open for enrollment. After this
            // date the course card disappears from the public site. Null = always shown.
            $table->date('enrollment_deadline')->nullable()->after('duration_months');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('enrollment_deadline');
        });
    }
};
