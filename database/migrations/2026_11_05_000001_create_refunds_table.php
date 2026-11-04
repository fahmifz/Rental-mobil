<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservasi_id')
                ->unique()
                ->constrained('reservasis')
                ->restrictOnDelete();
            $table->enum('jenis_refund', ['penuh', 'sebagian', 'tidak_ada']);
            $table->decimal('nominal_refund', 12, 2)->default(0);
            $table->enum('status', [
                'menunggu_pengembalian',
                'dikembalikan',
                'tidak_ada_refund',
            ]);
            $table->text('alasan');
            $table->timestamp('diputuskan_pada');
            $table->foreignId('diputuskan_oleh_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->enum('metode_refund', ['tunai', 'transfer'])->nullable();
            $table->timestamp('tanggal_refund')->nullable();
            $table->string('referensi_refund', 100)->nullable();
            $table->timestamp('dikonfirmasi_pada')->nullable();
            $table->foreignId('dikonfirmasi_oleh_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
