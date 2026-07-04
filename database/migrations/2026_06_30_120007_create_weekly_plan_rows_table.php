<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_plan_rows', function (Blueprint $table) {
            $table->id();
            $table->string('day_ar');
            $table->string('day_en')->nullable();
            $table->string('new_memorization_ar')->nullable();
            $table->string('new_memorization_en')->nullable();
            $table->string('review_ar')->nullable();
            $table->string('review_en')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_plan_rows');
    }
};
