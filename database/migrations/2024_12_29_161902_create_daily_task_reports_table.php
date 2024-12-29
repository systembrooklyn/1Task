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
            $table->dropForeign(['submitted_by']);
            $table->dropColumn('submitted_by');
        });
        Schema::create('daily_task_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_task_id')->constrained('daily_tasks')->onDelete('cascade');
            $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->enum('status', ['done', 'not_done'])->default('not_done');
            $table->timestamps();
            $table->unique(['daily_task_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_task_reports');
    }
};
