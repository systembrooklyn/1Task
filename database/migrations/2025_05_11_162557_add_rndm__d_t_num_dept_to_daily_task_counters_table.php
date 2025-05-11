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
        Schema::table('daily_task_counters', function (Blueprint $table) {
            $table->unsignedInteger('rndm_DT_num_dept')->default(2)->after('last_daily_task_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_task_counters', function (Blueprint $table) {
            $table->dropColumn('rndm_DT_num_dept');
        });
    }
};
