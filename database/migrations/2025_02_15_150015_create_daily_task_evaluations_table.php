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
        Schema::create('daily_task_evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('daily_task_id');
            $table->unsignedBigInteger('user_id');
            $table->string('comment')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->foreign('daily_task_id')
                    ->references('id')
                    ->on('daily_tasks')
                    ->onDelete('cascade');
            $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_task_evaluations');
    }
};
