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
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign('tasks_assigned_user_id_foreign');
            $table->dropForeign('tasks_consult_user_id_foreign');
            $table->dropForeign('tasks_inform_user_id_foreign');
            $table->dropColumn([
                'assigned_user_id',
                'consult_user_id',
                'inform_user_id'
            ]);
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_user_id')->nullable()->after('supervisor_user_id');
            $table->unsignedBigInteger('consult_user_id')->nullable()->after('assigned_user_id');
            $table->unsignedBigInteger('inform_user_id')->nullable()->after('consult_user_id');
            $table->foreign('assigned_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('consult_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('inform_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};
