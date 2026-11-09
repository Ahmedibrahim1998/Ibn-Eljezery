<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mobile app accounts:
 * - users.phone: students sign in to the app with their phone number.
 * - bookings.user_id: an enrollment made from the app belongs to that student
 *   (website bookings stay guest bookings with name/phone only).
 * - teachers.whatsapp_phone: shown to the teacher's confirmed students.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->unique()->after('email');
            // Students may register with a phone only (staff keep logging in by email).
            $table->string('email')->nullable()->change();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('course_id')->constrained()->nullOnDelete();
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->string('whatsapp_phone', 30)->nullable()->after('photo');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('whatsapp_phone');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropColumn('phone');
            $table->string('email')->nullable(false)->change();
        });
    }
};
