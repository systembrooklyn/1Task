<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plan_features', function (Blueprint $table) {
            $table->boolean('resettable')->default(true);
            $table->enum('reset_frequency',['daily', 'weekly', 'monthly'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_features', function (Blueprint $table) {
            $table->dropColumn('resettable');
            $table->dropColumn('reset_frequency');
        });
    }
};
