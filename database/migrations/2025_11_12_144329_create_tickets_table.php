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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('category');
            $table->text('description');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('status')->default(\App\Enums\TicketStatus::Open->value);
            $table->string('priority')->default(\App\Enums\TicketPriority::Medium->value);
            $table->timestamp('closed_at')->nullable();
            $table->string('ticket_number')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
