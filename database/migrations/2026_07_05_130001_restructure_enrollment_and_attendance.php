<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A "booking" now means an ENROLLMENT in a course (once), not a single session.
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'course_session_id')) {
                $table->dropConstrainedForeignId('course_session_id');
            }
            if (Schema::hasColumn('bookings', 'checked_in_at')) {
                $table->dropColumn(['checked_in_at', 'checked_out_at']);
            }
            $table->foreignId('course_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('months_paid')->default(0)->after('status');
        });

        // One row per attended class (day) per enrollment.
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->date('attended_on');
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('checked_out_at')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'attended_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
            $table->dropColumn('months_paid');
            $table->foreignId('course_session_id')->nullable()->constrained()->cascadeOnDelete();
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('checked_out_at')->nullable();
        });
    }
};
