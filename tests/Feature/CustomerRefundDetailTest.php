<?php

use App\Models\Mobil;
use App\Models\Pembayaran;
use App\Models\Refund;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function customerRefundDetailUser(): User
{
    return User::factory()->create(['role' => 'pelanggan']);
}

function customerRefundDetailReservation(User $customer, bool $withPayment = true): Reservasi
{
    static $sequence = 0;
    $sequence++;

    $mobil = Mobil::query()->create([
        'nama_mobil' => 'Mobil Detail Refund '.$sequence,
        'merek' => 'Toyota',
        'plat_nomor' => 'DD 97'.$sequence.' RFD',
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
        'status' => 'dibatalkan',
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

function customerRefundDetailConfirmedRefund(Reservasi $reservasi): Refund
{
    $decisionAdmin = User::factory()->create(['role' => 'admin']);
    $confirmationAdmin = User::factory()->create(['role' => 'admin']);

    return Refund::query()->create([
        'reservasi_id' => $reservasi->id,
        'jenis_refund' => 'penuh',
        'nominal_refund' => 700000,
        'status' => 'dikembalikan',
        'alasan' => 'Reservasi dibatalkan oleh Admin.',
        'diputuskan_pada' => now()->subHour(),
        'diputuskan_oleh_id' => $decisionAdmin->id,
        'metode_refund' => 'transfer',
        'tanggal_refund' => now()->subMinutes(30),
        'referensi_refund' => 'TRF-CUSTOMER-001',
        'dikonfirmasi_pada' => now()->subMinutes(20),
        'dikonfirmasi_oleh_id' => $confirmationAdmin->id,
    ]);
}

test('a customer sees their confirmed refund and cancelled reservation financial summary', function () {
    $customer = customerRefundDetailUser();
    $reservasi = customerRefundDetailReservation($customer);
    customerRefundDetailConfirmedRefund($reservasi);

    $this->actingAs($customer)
        ->get(route('reservasi.show', $reservasi))
        ->assertOk()
        ->assertSee('Informasi Refund')
        ->assertSee('Pembayaran masuk')
        ->assertSee('Refund sudah dikembalikan')
        ->assertSee('Saldo pembayaran bersih')
        ->assertSee('Rp700.000')
        ->assertSee('Reservasi dibatalkan oleh Admin.')
        ->assertSee('Dikembalikan')
        ->assertSee('Transfer')
        ->assertDontSee('Sisa tagihan');
});

test('a cancelled reservation clearly handles missing legacy refund and no-payment records', function () {
    $customer = customerRefundDetailUser();
    $paidWithoutRefund = customerRefundDetailReservation($customer);
    $unpaidWithoutRefund = customerRefundDetailReservation($customer, false);

    $this->actingAs($customer)
        ->get(route('reservasi.show', $paidWithoutRefund))
        ->assertOk()
        ->assertSee('Keputusan refund belum tercatat')
        ->assertDontSee('Sisa tagihan');

    $this->actingAs($customer)
        ->get(route('reservasi.show', $unpaidWithoutRefund))
        ->assertOk()
        ->assertSee('Belum ada pembayaran masuk, sehingga tidak ada transaksi refund.')
        ->assertDontSee('Sisa tagihan');
});

test('a customer cannot view another customers refund information', function () {
    $owner = customerRefundDetailUser();
    $otherCustomer = customerRefundDetailUser();
    $reservasi = customerRefundDetailReservation($owner);
    customerRefundDetailConfirmedRefund($reservasi);

    $this->actingAs($otherCustomer)
        ->get(route('reservasi.show', $reservasi))
        ->assertNotFound();
});
