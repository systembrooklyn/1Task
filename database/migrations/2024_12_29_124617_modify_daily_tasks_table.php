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
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->enum('status', ['done', 'not_done'])->nullable()->default(null)->change();
            $table->boolean('active')->default(true)->after('status');
            $table->unsignedBigInteger('submitted_by')->nullable()->after('active');
            $table->foreign('submitted_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->enum('status', ['done', 'not_done'])->default('not_done')->nullable(false)->change();
            $table->dropColumn('active');
            $table->dropForeign(['submitted_by']);
            $table->dropColumn('submitted_by');
        });
    }
};
