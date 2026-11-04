<?php

use App\Models\Mobil;
use App\Models\Pembayaran;
use App\Models\Refund;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function refundModelReservasi(User $pelanggan): Reservasi
{
    static $sequence = 0;
    $sequence++;

    $mobil = Mobil::query()->create([
        'nama_mobil' => 'Mobil Refund',
        'merek' => 'Toyota',
        'plat_nomor' => 'DD 91'.$sequence.' RFD',
        'kapasitas_penumpang' => 5,
        'harga_sewa_per_hari' => 350000,
        'status' => 'tersedia',
    ]);

    return $pelanggan->reservasis()->create([
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => today()->addDays(2)->toDateString(),
        'tanggal_selesai' => today()->addDays(3)->toDateString(),
        'durasi_sewa' => 2,
        'tarif_harian' => 350000,
        'total_biaya' => 700000,
        'status' => 'dibatalkan',
    ]);
}

function refundModelPembayaran(Reservasi $reservasi, float $nominal): Pembayaran
{
    return Pembayaran::query()->create([
        'reservasi_id' => $reservasi->id,
        'nominal_pembayaran' => $nominal,
        'tanggal_pembayaran' => today()->toDateString(),
        'metode_pembayaran' => 'transfer',
        'status' => 'lunas',
    ]);
}

test('a pending refund retains its decision and reservation relationships', function () {
    $pelanggan = User::factory()->create(['role' => 'pelanggan']);
    $admin = User::factory()->create(['role' => 'admin']);
    $reservasi = refundModelReservasi($pelanggan);
    refundModelPembayaran($reservasi, 700000);
    $reservasi->update([
        'dibatalkan_pada' => now(),
        'dibatalkan_oleh_id' => $admin->id,
        'alasan_pembatalan' => 'Permintaan pembatalan telah disetujui.',
    ]);

    $refund = Refund::query()->create([
        'reservasi_id' => $reservasi->id,
        'jenis_refund' => 'sebagian',
        'nominal_refund' => 200000,
        'status' => 'menunggu_pengembalian',
        'alasan' => 'Pembatalan disetujui dengan potongan administrasi.',
        'diputuskan_pada' => now(),
        'diputuskan_oleh_id' => $admin->id,
    ]);

    expect($refund->reservasi->id)->toBe($reservasi->id)
        ->and($refund->diputuskanOleh->id)->toBe($admin->id)
        ->and($reservasi->refund->id)->toBe($refund->id)
        ->and($reservasi->fresh()->dibatalkanOleh->id)->toBe($admin->id)
        ->and($admin->refundDiputuskan->pluck('id'))->toContain($refund->id);
});

test('refund model rejects invalid no-refund and unconfirmed paid-refund states', function () {
    $pelanggan = User::factory()->create(['role' => 'pelanggan']);
    $admin = User::factory()->create(['role' => 'admin']);

    expect(fn () => Refund::query()->create([
        'reservasi_id' => refundModelReservasi($pelanggan)->id,
        'jenis_refund' => 'tidak_ada',
        'nominal_refund' => 1,
        'status' => 'tidak_ada_refund',
        'alasan' => 'Tidak memenuhi syarat refund.',
        'diputuskan_pada' => now(),
        'diputuskan_oleh_id' => $admin->id,
    ]))->toThrow(\InvalidArgumentException::class);

    $reservasi = refundModelReservasi($pelanggan);
    refundModelPembayaran($reservasi, 700000);

    expect(fn () => Refund::query()->create([
        'reservasi_id' => $reservasi->id,
        'jenis_refund' => 'penuh',
        'nominal_refund' => 700000,
        'status' => 'dikembalikan',
        'alasan' => 'Refund penuh disetujui.',
        'diputuskan_pada' => now(),
        'diputuskan_oleh_id' => $admin->id,
    ]))->toThrow(\InvalidArgumentException::class);
});
