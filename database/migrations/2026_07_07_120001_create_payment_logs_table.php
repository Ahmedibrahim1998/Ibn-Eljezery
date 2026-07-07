<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A historical record written each time a student's monthly subscription
        // is marked paid. Fields are denormalized (snapshots) so the log survives
        // even if the enrollment or course is later deleted.
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('course_title_ar')->nullable();
            $table->string('course_title_en')->nullable();
            $table->string('student_name');
            $table->string('student_phone')->nullable();
            $table->unsignedInteger('month_number');       // which paid month (1, 2, 3, ...)
            $table->unsignedInteger('classes_attended');   // attended classes at payment time
            $table->dateTime('paid_at');
            $table->timestamps();

            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
