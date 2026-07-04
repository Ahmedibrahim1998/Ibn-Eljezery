<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('age_group')->nullable();
            $table->string('level')->nullable();
            $table->string('program')->nullable();
            $table->text('message')->nullable();
            $table->string('source')->default('contact'); // LeadSourceEnum
            $table->string('status')->default('new');      // LeadStatusEnum
            $table->timestamps();

            $table->index('status');
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
