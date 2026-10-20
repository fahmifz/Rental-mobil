<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
public function up(): void
{
    Schema::create('mobil', function (Blueprint $table) {
        $table->id();

        $table->foreignId('kategori_mobil_id')
            ->nullable()
            ->constrained('kategori_mobil')
            ->nullOnDelete();

        $table->string('nama_mobil');
        $table->string('merek');
        $table->string('tipe')->nullable();
        $table->string('plat_nomor')->unique();
        $table->year('tahun')->nullable();
        $table->string('transmisi')->nullable();
        $table->unsignedTinyInteger('kapasitas_penumpang')->default(5);
        $table->decimal('harga_sewa_per_hari', 12, 2);
        $table->text('deskripsi')->nullable();
        $table->string('foto')->nullable();

        $table->enum('status', [
            'tersedia',
            'disewa',
            'perawatan',
        ])->default('tersedia');

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('mobil');
}

};
