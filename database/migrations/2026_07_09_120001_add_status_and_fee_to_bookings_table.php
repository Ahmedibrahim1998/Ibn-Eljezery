<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Social status of the student (orphan/poor are exempt from fees).
            $table->string('student_status')->default('regular')->after('email');
            // Monthly subscription amount (null for exempt students).
            $table->decimal('monthly_fee', 8, 2)->nullable()->after('student_status');
            // When the last monthly payment was recorded (for "paid month X").
            $table->dateTime('last_paid_at')->nullable()->after('months_paid');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['student_status', 'monthly_fee', 'last_paid_at']);
        });
    }
};
