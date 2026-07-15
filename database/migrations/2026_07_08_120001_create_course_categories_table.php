<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A "course" (parent). Each course belongs to a type (offline/online) and
        // contains several "groups" (the existing `courses` rows, one per teacher).
        Schema::create('course_categories', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('offline'); // CourseTypeEnum: offline|online
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });

        // Link each group to its parent course.
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('course_category_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_category_id');
        });

        Schema::dropIfExists('course_categories');
    }
};
