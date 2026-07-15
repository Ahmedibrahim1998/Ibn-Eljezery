<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_logs', function (Blueprint $table) {
            // The amount paid for this month (snapshot of the student's monthly_fee).
            $table->decimal('amount', 8, 2)->nullable()->after('classes_attended');
        });
    }

    public function down(): void
    {
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }
};
