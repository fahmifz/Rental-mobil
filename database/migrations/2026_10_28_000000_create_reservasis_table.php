<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('mobil_id')
                ->constrained('mobil')
                ->restrictOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->unsignedSmallInteger('durasi_sewa');
            $table->decimal('tarif_harian', 12, 2);
            $table->decimal('total_biaya', 12, 2);
            $table->enum('status', [
                'menunggu_konfirmasi',
                'disetujui',
                'ditolak',
                'dibatalkan',
                'selesai',
            ])->default('menunggu_konfirmasi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservasis');
    }
};
