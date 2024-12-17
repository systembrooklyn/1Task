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
        Schema::create('daily_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_no')->unique();
            $table->string('task_name');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->enum('task_type', ['single', 'daily', 'weekly', 'monthly', 'last_day_of_month']);
            $table->json('recurrent_days')->nullable();
            $table->integer('day_of_month')->nullable();
            $table->time('from');
            $table->time('to');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('dept_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['done', 'not_done'])->default('not_done');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('dept_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_tasks');
    }
};
