<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parkings', function (Blueprint $table): void {
            $table->string('yape_qr_path')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('parkings', function (Blueprint $table): void {
            $table->dropColumn('yape_qr_path');
        });
    }
};