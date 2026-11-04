<?php

use App\Models\Mobil;
use App\Models\Pembayaran;
use App\Models\Refund;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function cancellationRefundAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

function cancellationRefundCustomer(): User
{
    return User::factory()->create(['role' => 'pelanggan']);
}

function cancellationRefundReservasi(User $customer): Reservasi
{
    static $sequence = 0;
    $sequence++;

    $mobil = Mobil::query()->create([
        'nama_mobil' => 'Mobil Pembatalan '.$sequence,
        'merek' => 'Toyota',
        'plat_nomor' => 'DD 92'.$sequence.' RFD',
        'kapasitas_penumpang' => 5,
        'harga_sewa_per_hari' => 350000,
        'status' => 'tersedia',
    ]);

    return $customer->reservasis()->create([
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => today()->addDays(2)->toDateString(),
        'tanggal_selesai' => today()->addDays(3)->toDateString(),
        'durasi_sewa' => 2,
        'tarif_harian' => 350000,
        'total_biaya' => 700000,
        'status' => 'disetujui',
    ]);
}

function cancellationRefundPayment(Reservasi $reservasi, float $nominal = 700000): Pembayaran
{
    return Pembayaran::query()->create([
        'reservasi_id' => $reservasi->id,
        'nominal_pembayaran' => $nominal,
        'tanggal_pembayaran' => today()->toDateString(),
        'metode_pembayaran' => 'transfer',
        'status' => 'lunas',
    ]);
}

function cancellationRefundDecision(string $jenis, float $nominal): array
{
    return [
        'jenis_refund' => $jenis,
        'nominal_refund' => $nominal,
        'alasan_refund' => 'Keputusan refund dicatat oleh admin.',
    ];
}

test('an admin cancels an unpaid reservation directly and records cancellation metadata', function () {
    $admin = cancellationRefundAdmin();
    $reservasi = cancellationRefundReservasi(cancellationRefundCustomer());

    $this->actingAs($admin)
        ->post(route('admin.reservasi.cancel', $reservasi), [
            'alasan_pembatalan' => 'Jadwal kendaraan tidak tersedia.',
        ])
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('reservasis', [
        'id' => $reservasi->id,
        'status' => 'dibatalkan',
        'dibatalkan_oleh_id' => $admin->id,
        'alasan_pembatalan' => 'Jadwal kendaraan tidak tersedia.',
    ]);
    $this->assertDatabaseMissing('refunds', [
        'reservasi_id' => $reservasi->id,
    ]);

    expect($reservasi->fresh()->dibatalkan_pada)->not->toBeNull();
});

test('an admin records full partial and no-refund decisions atomically with cancellation', function () {
    $admin = cancellationRefundAdmin();

    foreach ([
        ['penuh', 700000, 'menunggu_pengembalian'],
        ['sebagian', 200000, 'menunggu_pengembalian'],
        ['tidak_ada', 0, 'tidak_ada_refund'],
    ] as [$jenis, $nominal, $status]) {
        $reservasi = cancellationRefundReservasi(cancellationRefundCustomer());
        cancellationRefundPayment($reservasi);

        $this->actingAs($admin)
            ->post(route('admin.reservasi.cancel', $reservasi), cancellationRefundDecision($jenis, $nominal))
            ->assertRedirect(route('admin.reservasi.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reservasis', [
            'id' => $reservasi->id,
            'status' => 'dibatalkan',
            'dibatalkan_oleh_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('refunds', [
            'reservasi_id' => $reservasi->id,
            'jenis_refund' => $jenis,
            'nominal_refund' => $nominal,
            'status' => $status,
            'diputuskan_oleh_id' => $admin->id,
        ]);
    }
});

test('a paid reservation cannot be cancelled without a complete refund decision', function () {
    $admin = cancellationRefundAdmin();
    $reservasi = cancellationRefundReservasi(cancellationRefundCustomer());
    cancellationRefundPayment($reservasi);

    $this->actingAs($admin)
        ->from(route('admin.reservasi.index'))
        ->post(route('admin.reservasi.cancel', $reservasi))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHasErrors(['jenis_refund', 'nominal_refund', 'alasan_refund']);

    expect($reservasi->fresh()->status)->toBe('disetujui')
        ->and(Refund::query()->where('reservasi_id', $reservasi->id)->exists())->toBeFalse();
});

test('an invalid or duplicate refund decision leaves the reservation and payments intact', function () {
    $admin = cancellationRefundAdmin();
    $reservasi = cancellationRefundReservasi(cancellationRefundCustomer());
    $payment = cancellationRefundPayment($reservasi);

    $this->actingAs($admin)
        ->from(route('admin.reservasi.index'))
        ->post(route('admin.reservasi.cancel', $reservasi), cancellationRefundDecision('penuh', 700000.01))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHasErrors('refund');

    expect($reservasi->fresh()->status)->toBe('disetujui')
        ->and(Refund::query()->where('reservasi_id', $reservasi->id)->exists())->toBeFalse();
    $this->assertDatabaseHas('pembayarans', [
        'id' => $payment->id,
        'nominal_pembayaran' => 700000,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.reservasi.cancel', $reservasi), cancellationRefundDecision('penuh', 700000))
        ->assertRedirect(route('admin.reservasi.index'));

    $this->actingAs($admin)
        ->from(route('admin.reservasi.index'))
        ->post(route('admin.reservasi.cancel', $reservasi), cancellationRefundDecision('penuh', 700000))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHasErrors('reservasi');

    expect(Refund::query()->where('reservasi_id', $reservasi->id)->count())->toBe(1)
        ->and(Pembayaran::query()->whereKey($payment->id)->value('nominal_pembayaran'))->toBe('700000.00');
});
