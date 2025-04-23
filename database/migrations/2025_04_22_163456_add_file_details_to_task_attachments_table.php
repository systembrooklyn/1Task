<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileDetailsToTaskAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task_attachments', function (Blueprint $table) {
            $table->string('file_name')->after('file_path')->nullable();
            $table->decimal('file_size')->after('file_name')->nullable();
            $table->string('download_url')->after('file_size')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_attachments', function (Blueprint $table) {
            $table->dropColumn('file_name');
            $table->dropColumn('file_size');
            $table->dropColumn('download_url');
        });
    }
}