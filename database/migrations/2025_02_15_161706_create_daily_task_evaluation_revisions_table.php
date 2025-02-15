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
        Schema::create('daily_task_evaluation_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('daily_task_evaluation_id');
            $table->unsignedBigInteger('user_id');  
            $table->string('field_name')->nullable();           
            $table->text('old_value')->nullable();              
            $table->text('new_value')->nullable();              
            $table->timestamps();
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // Foreign key to evaluations
            $table->foreign('daily_task_evaluation_id')
                  ->references('id')
                  ->on('daily_task_evaluations')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_task_evaluation_revisions');
    }
};
