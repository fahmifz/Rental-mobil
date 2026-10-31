<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservasi_id')
                ->unique()
                ->constrained('reservasis')
                ->restrictOnDelete();
            $table->decimal('nominal_pembayaran', 12, 2);
            $table->date('tanggal_pembayaran');
            $table->enum('metode_pembayaran', ['tunai', 'transfer']);
            $table->enum('status', [
                'belum_dibayar',
                'dibayar_sebagian',
                'lunas',
            ]);
            $table->text('catatan')->nullable();
            $table->foreignId('dicatat_oleh_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
