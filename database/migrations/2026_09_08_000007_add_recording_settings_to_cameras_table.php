<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cameras', function (Blueprint $table): void {
            $table->boolean('recording_enabled')->default(false)->after('active');
            $table->enum('recording_mode', ['none', 'manual', 'continuous', 'motion', 'scheduled'])->default('manual')->after('recording_enabled');
            $table->unsignedTinyInteger('retention_days')->default(7)->after('recording_mode');
            $table->unsignedInteger('storage_quota_mb')->default(1024)->after('retention_days');
            $table->string('resolution')->default('640x480')->after('storage_quota_mb');
            $table->unsignedTinyInteger('fps')->default(15)->after('resolution');
            $table->time('schedule_start')->nullable()->after('fps');
            $table->time('schedule_end')->nullable()->after('schedule_start');
            $table->boolean('flip_horizontal')->default(false)->after('schedule_end');
            $table->boolean('flip_vertical')->default(false)->after('flip_horizontal');
            $table->boolean('motion_detection')->default(false)->after('flip_vertical');
            $table->boolean('ptz_enabled')->default(false)->after('motion_detection');
        });
    }

    public function down(): void
    {
        Schema::table('cameras', function (Blueprint $table): void {
            $table->dropColumn([
                'recording_enabled', 'recording_mode', 'retention_days', 'storage_quota_mb',
                'resolution', 'fps', 'schedule_start', 'schedule_end', 'flip_horizontal',
                'flip_vertical', 'motion_detection', 'ptz_enabled',
            ]);
        });
    }
};