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
        Schema::table('features', function (Blueprint $table) {
            $table->enum('unit', ['count', 'kb', 'mb'])->default('count')->after('slug');
            $table->enum('reset_frequency',['daily', 'weekly', 'monthly'])->nullable()->after('unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('features', function (Blueprint $table) {
            $table->dropColumn('unit');
            $table->dropColumn('reset_frequency');
        });
    }
};
