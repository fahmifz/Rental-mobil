<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->string('idempotency_key', 100)->nullable()->after('catatan');
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->unique(
                ['reservasi_id', 'idempotency_key'],
                'pembayarans_reservasi_idempotency_unique'
            );
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropUnique('pembayarans_reservasi_id_unique');
        });
    }

    public function down(): void
    {
        $hasMultipleTransactions = DB::table('pembayarans')
            ->select('reservasi_id')
            ->groupBy('reservasi_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasMultipleTransactions) {
            throw new \RuntimeException(
                'Rollback dibatalkan: sudah terdapat lebih dari satu transaksi pembayaran untuk reservasi yang sama. '
                . 'Skema satu pembayaran per reservasi tidak dapat dipulihkan secara otomatis.'
            );
        }

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->unique(
                'reservasi_id',
                'pembayarans_reservasi_id_unique'
            );
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropUnique('pembayarans_reservasi_idempotency_unique');
            $table->dropColumn('idempotency_key');
        });
    }
};
