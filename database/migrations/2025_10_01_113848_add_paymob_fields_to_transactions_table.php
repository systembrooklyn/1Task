<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('status')->default('unknown')->after('success');
            $table->boolean('pending')->default(false)->after('status');
            $table->boolean('is_refunded')->default(false)->after('pending');
            $table->boolean('is_voided')->default(false)->after('is_refunded');
            $table->integer('refunded_amount_cents')->default(0)->after('is_voided');
            $table->text('raw_response')->nullable()->after('refunded_amount_cents');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'pending',
                'is_refunded',
                'is_voided',
                'refunded_amount_cents',
                'raw_response'
            ]);
        });
    }
};
