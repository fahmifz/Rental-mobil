<?php

use App\Models\Mobil;
use App\Models\Pembayaran;
use App\Models\Refund;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function refundUiUser(string $role): User
{
    return User::factory()->create(['role' => $role]);
}

function refundUiReservasi(bool $withPayment = true): Reservasi
{
    static $sequence = 0;
    $sequence++;

    $customer = refundUiUser('pelanggan');
    $mobil = Mobil::query()->create([
        'nama_mobil' => 'Mobil UI Refund '.$sequence,
        'merek' => 'Toyota',
        'plat_nomor' => 'DD 95'.$sequence.' RFD',
        'kapasitas_penumpang' => 5,
        'harga_sewa_per_hari' => 350000,
        'status' => 'tersedia',
    ]);
    $reservasi = $customer->reservasis()->create([
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => today()->addDays(2)->toDateString(),
        'tanggal_selesai' => today()->addDays(3)->toDateString(),
        'durasi_sewa' => 2,
        'tarif_harian' => 350000,
        'total_biaya' => 700000,
        'status' => 'disetujui',
    ]);

    if ($withPayment) {
        Pembayaran::query()->create([
            'reservasi_id' => $reservasi->id,
            'nominal_pembayaran' => 700000,
            'tanggal_pembayaran' => today()->toDateString(),
            'metode_pembayaran' => 'transfer',
            'status' => 'lunas',
        ]);
    }

    return $reservasi;
}

function refundUiPendingRefund(): Refund
{
    $decisionAdmin = refundUiUser('admin');
    $reservasi = refundUiReservasi();
    $reservasi->update([
        'status' => 'dibatalkan',
        'dibatalkan_pada' => now(),
        'dibatalkan_oleh_id' => $decisionAdmin->id,
    ]);

    return Refund::query()->create([
        'reservasi_id' => $reservasi->id,
        'jenis_refund' => 'penuh',
        'nominal_refund' => 700000,
        'status' => 'menunggu_pengembalian',
        'alasan' => 'Pembatalan dicatat untuk pengujian tampilan.',
        'diputuskan_pada' => now(),
        'diputuskan_oleh_id' => $decisionAdmin->id,
    ]);
}

test('admin can open the paid cancellation refund decision form', function () {
    $admin = refundUiUser('admin');
    $reservasi = refundUiReservasi();

    $this->actingAs($admin)
        ->get(route('admin.refund.create', $reservasi))
        ->assertOk()
        ->assertSee('Batalkan Reservasi dan Tentukan Refund')
        ->assertSee('Pembayaran yang diterima: Rp700.000')
        ->assertSee(route('admin.reservasi.cancel', $reservasi), false);
});

test('admin can view a refund list and pending refund confirmation form', function () {
    $admin = refundUiUser('admin');
    $refund = refundUiPendingRefund();

    $this->actingAs($admin)
        ->get(route('admin.refund.index'))
        ->assertOk()
        ->assertSee('Daftar Refund')
        ->assertSee((string) $refund->reservasi_id)
        ->assertSee(route('admin.refund.show', $refund), false);

    $this->actingAs($admin)
        ->get(route('admin.refund.show', $refund))
        ->assertOk()
        ->assertSee('Konfirmasi Pengembalian Dana')
        ->assertSee(route('admin.refund.confirm', $refund), false);
});

test('refund read pages are restricted to admins and paid form rejects unpaid reservations', function () {
    $refund = refundUiPendingRefund();
    $unpaidReservasi = refundUiReservasi(false);

    $this->get(route('admin.refund.index'))
        ->assertRedirect(route('login'));

    $this->actingAs(refundUiUser('pelanggan'))
        ->get(route('admin.refund.show', $refund))
        ->assertForbidden();

    $this->actingAs(refundUiUser('admin'))
        ->get(route('admin.refund.create', $unpaidReservasi))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHas('error');
});
