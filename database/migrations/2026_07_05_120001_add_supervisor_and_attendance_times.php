<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('supervisor_id')->nullable()->after('teacher_id')
                ->constrained('users')->nullOnDelete();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dateTime('checked_in_at')->nullable()->after('status');
            $table->dateTime('checked_out_at')->nullable()->after('checked_in_at');
        });

        // The simple attendance enum is replaced by check-in/out timestamps.
        if (Schema::hasColumn('bookings', 'attendance')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('attendance');
            });
        }
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supervisor_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['checked_in_at', 'checked_out_at']);
            $table->string('attendance')->default('pending');
        });
    }
};
