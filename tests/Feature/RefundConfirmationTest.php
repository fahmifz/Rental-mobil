<?php

use App\Models\Mobil;
use App\Models\Pembayaran;
use App\Models\Refund;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function refundConfirmationUser(string $role): User
{
    return User::factory()->create(['role' => $role]);
}

function refundConfirmationPendingRefund(string $jenis = 'penuh'): Refund
{
    static $sequence = 0;
    $sequence++;

    $customer = refundConfirmationUser('pelanggan');
    $decisionAdmin = refundConfirmationUser('admin');
    $mobil = Mobil::query()->create([
        'nama_mobil' => 'Mobil Konfirmasi Refund '.$sequence,
        'merek' => 'Toyota',
        'plat_nomor' => 'DD 93'.$sequence.' RFD',
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
        'dibatalkan_pada' => now(),
        'dibatalkan_oleh_id' => $decisionAdmin->id,
    ]);

    if ($jenis === 'tidak_ada') {
        return Refund::query()->create([
            'reservasi_id' => $reservasi->id,
            'jenis_refund' => 'tidak_ada',
            'nominal_refund' => 0,
            'status' => 'tidak_ada_refund',
            'alasan' => 'Tidak ada refund.',
            'diputuskan_pada' => now(),
            'diputuskan_oleh_id' => $decisionAdmin->id,
        ]);
    }

    Pembayaran::query()->create([
        'reservasi_id' => $reservasi->id,
        'nominal_pembayaran' => 700000,
        'tanggal_pembayaran' => today()->toDateString(),
        'metode_pembayaran' => 'transfer',
        'status' => 'lunas',
    ]);

    return Refund::query()->create([
        'reservasi_id' => $reservasi->id,
        'jenis_refund' => 'penuh',
        'nominal_refund' => 700000,
        'status' => 'menunggu_pengembalian',
        'alasan' => 'Refund penuh disetujui.',
        'diputuskan_pada' => now(),
        'diputuskan_oleh_id' => $decisionAdmin->id,
    ]);
}

function refundConfirmationPayload(array $overrides = []): array
{
    return array_merge([
        'metode_refund' => 'transfer',
        'tanggal_refund' => now()->subMinute()->toDateTimeString(),
        'referensi_refund' => 'TRF-REF-001',
    ], $overrides);
}

test('an admin confirms a pending refund without changing its amount', function () {
    $admin = refundConfirmationUser('admin');
    $refund = refundConfirmationPendingRefund();
    $nominalAwal = $refund->nominal_refund;

    $this->actingAs($admin)
        ->post(route('admin.refund.confirm', $refund), refundConfirmationPayload([
            'nominal_refund' => 1,
        ]))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHas('success', 'Refund berhasil dikonfirmasi.');

    $confirmed = $refund->fresh();
    expect($confirmed->status)->toBe('dikembalikan')
        ->and($confirmed->nominal_refund)->toBe($nominalAwal)
        ->and($confirmed->metode_refund)->toBe('transfer')
        ->and($confirmed->referensi_refund)->toBe('TRF-REF-001')
        ->and($confirmed->dikonfirmasi_oleh_id)->toBe($admin->id)
        ->and($confirmed->dikonfirmasi_pada)->not->toBeNull();
});

test('refund confirmation validates transfer reference and rejects invalid refund statuses', function () {
    $admin = refundConfirmationUser('admin');
    $pendingRefund = refundConfirmationPendingRefund();

    $this->actingAs($admin)
        ->from(route('admin.reservasi.index'))
        ->post(route('admin.refund.confirm', $pendingRefund), refundConfirmationPayload([
            'referensi_refund' => '',
        ]))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHasErrors('referensi_refund');

    expect($pendingRefund->fresh()->status)->toBe('menunggu_pengembalian');

    $futureRefund = refundConfirmationPendingRefund();
    $this->actingAs($admin)
        ->from(route('admin.reservasi.index'))
        ->post(route('admin.refund.confirm', $futureRefund), refundConfirmationPayload([
            'tanggal_refund' => now()->addDay()->toDateTimeString(),
        ]))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHasErrors('tanggal_refund');

    expect($futureRefund->fresh()->status)->toBe('menunggu_pengembalian');

    $withoutRefund = refundConfirmationPendingRefund('tidak_ada');
    $this->actingAs($admin)
        ->from(route('admin.reservasi.index'))
        ->post(route('admin.refund.confirm', $withoutRefund), refundConfirmationPayload([
            'metode_refund' => 'tunai',
            'referensi_refund' => null,
        ]))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHasErrors('refund');

    expect($withoutRefund->fresh()->status)->toBe('tidak_ada_refund');
});

test('guests and customers cannot confirm a refund', function () {
    $refund = refundConfirmationPendingRefund();

    $this->post(route('admin.refund.confirm', $refund), refundConfirmationPayload())
        ->assertRedirect(route('login'));

    $this->actingAs(refundConfirmationUser('pelanggan'))
        ->post(route('admin.refund.confirm', $refund), refundConfirmationPayload())
        ->assertForbidden();

    expect($refund->fresh()->status)->toBe('menunggu_pengembalian');
});

test('a refund cannot be confirmed twice', function () {
    $admin = refundConfirmationUser('admin');
    $refund = refundConfirmationPendingRefund();

    $this->actingAs($admin)
        ->post(route('admin.refund.confirm', $refund), refundConfirmationPayload());

    $confirmedAt = $refund->fresh()->dikonfirmasi_pada;

    $this->actingAs($admin)
        ->from(route('admin.reservasi.index'))
        ->post(route('admin.refund.confirm', $refund), refundConfirmationPayload([
            'referensi_refund' => 'TRF-REF-002',
        ]))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHasErrors('refund');

    $confirmed = $refund->fresh();
    expect($confirmed->status)->toBe('dikembalikan')
        ->and($confirmed->referensi_refund)->toBe('TRF-REF-001')
        ->and($confirmed->dikonfirmasi_pada->toDateTimeString())
        ->toBe($confirmedAt->toDateTimeString());
});
