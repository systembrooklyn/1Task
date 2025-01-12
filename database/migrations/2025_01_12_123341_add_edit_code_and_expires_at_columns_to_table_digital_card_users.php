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
        Schema::table('digital_card_users', function (Blueprint $table) {
            $table->string('edit_code')->nullable()->unique();
            $table->timestamp('edit_code_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('digital_card_users', function (Blueprint $table) {
            $table->dropColumn(['edit_code', 'edit_code_expires_at']);
        });
    }
};
