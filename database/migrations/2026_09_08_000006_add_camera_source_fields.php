<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cameras', function (Blueprint $table): void {
            $table->string('source_type')->default('rtsp')->after('stream_url');
            $table->string('type')->default('cctv')->after('source_type');
            $table->timestamp('last_heartbeat_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('cameras', function (Blueprint $table): void {
            $table->dropColumn(['source_type', 'type', 'last_heartbeat_at']);
        });
    }
};