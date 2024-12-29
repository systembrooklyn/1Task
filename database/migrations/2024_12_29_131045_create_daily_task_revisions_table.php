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
        Schema::create('daily_task_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User who made the revision
            $table->string('field_name'); // Name of the changed field
            $table->text('old_value')->nullable(); // Old value of the field
            $table->text('new_value')->nullable(); // New value of the field
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_task_revisions');
    }
};
