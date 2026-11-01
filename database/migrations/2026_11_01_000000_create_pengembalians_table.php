<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengembalians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservasi_id')
                ->unique()
                ->constrained('reservasis')
                ->restrictOnDelete();
            $table->dateTime('tanggal_pengembalian');
            $table->string('kondisi_mobil', 50);
            $table->text('catatan_kondisi')->nullable();
            $table->unsignedInteger('durasi_keterlambatan')->default(0);
            $table->decimal('tarif_denda_per_hari', 12, 2)->default(0);
            $table->decimal('total_denda', 12, 2)->default(0);
            $table->enum('status_mobil_setelah_kembali', ['tersedia', 'perawatan']);
            $table->foreignId('diproses_oleh_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengembalians');
    }
};

