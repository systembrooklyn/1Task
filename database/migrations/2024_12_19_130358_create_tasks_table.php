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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                  ->constrained()
                  ->cascadeOnDelete();
        
            $table->foreignId('project_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();
        
            $table->foreignId('department_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();
        
            $table->foreignId('creator_user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
        
            $table->foreignId('assigned_user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
        
            $table->foreignId('supervisor_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->string('title');
            $table->text('description');
            $table->date('start_date');
            $table->date('deadline');
            $table->boolean('is_urgent')->default(false);
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->enum('status', ['pending', 'rework', 'done', 'review', 'inProgress'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
