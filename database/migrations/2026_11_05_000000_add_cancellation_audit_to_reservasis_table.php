<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservasis', function (Blueprint $table) {
            $table->timestamp('dibatalkan_pada')->nullable()->after('status');
            $table->foreignId('dibatalkan_oleh_id')
                ->nullable()
                ->after('dibatalkan_pada')
                ->constrained('users')
                ->nullOnDelete();
            $table->text('alasan_pembatalan')->nullable()->after('dibatalkan_oleh_id');
        });
    }

    public function down(): void
    {
        Schema::table('reservasis', function (Blueprint $table) {
            $table->dropForeign(['dibatalkan_oleh_id']);
            $table->dropColumn([
                'dibatalkan_pada',
                'dibatalkan_oleh_id',
                'alasan_pembatalan',
            ]);
        });
    }
};
