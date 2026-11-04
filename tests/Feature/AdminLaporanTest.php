<?php

use App\Models\Mobil;
use App\Models\Pembayaran;
use App\Models\Refund;
use App\Models\Reservasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function laporanUser(string $role): User
{
    return User::factory()->create(['role' => $role]);
}

function laporanMobil(): Mobil
{
    static $sequence = 0;
    $sequence++;

    return Mobil::query()->create([
        'nama_mobil' => 'Mobil Laporan '.$sequence,
        'merek' => 'Toyota',
        'plat_nomor' => 'DD 96'.$sequence.' LPR',
        'kapasitas_penumpang' => 5,
        'harga_sewa_per_hari' => 350000,
        'status' => 'tersedia',
    ]);
}

function laporanReservasi(User $customer, string $status, Carbon $createdAt, float $total = 700000): Reservasi
{
    $reservasi = $customer->reservasis()->create([
        'mobil_id' => laporanMobil()->id,
        'tanggal_mulai' => $createdAt->copy()->addDays(2)->toDateString(),
        'tanggal_selesai' => $createdAt->copy()->addDays(3)->toDateString(),
        'durasi_sewa' => 2,
        'tarif_harian' => $total / 2,
        'total_biaya' => $total,
        'status' => $status,
    ]);
    $reservasi->forceFill([
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ])->save();

    return $reservasi->fresh();
}

function laporanPembayaran(Reservasi $reservasi, float $nominal, string $tanggal): Pembayaran
{
    return Pembayaran::query()->create([
        'reservasi_id' => $reservasi->id,
        'nominal_pembayaran' => $nominal,
        'tanggal_pembayaran' => $tanggal,
        'metode_pembayaran' => 'transfer',
        'status' => 'lunas',
    ]);
}

function laporanRefund(
    Reservasi $reservasi,
    User $admin,
    float $nominal,
    string $status,
    ?Carbon $tanggalRefund = null,
): Refund {
    return Refund::query()->create([
        'reservasi_id' => $reservasi->id,
        'jenis_refund' => 'sebagian',
        'nominal_refund' => $nominal,
        'status' => $status,
        'alasan' => 'Data refund untuk laporan.',
        'diputuskan_pada' => now(),
        'diputuskan_oleh_id' => $admin->id,
        'metode_refund' => $status === 'dikembalikan' ? 'transfer' : null,
        'tanggal_refund' => $tanggalRefund,
        'referensi_refund' => $status === 'dikembalikan' ? 'TRF-LAPORAN-001' : null,
        'dikonfirmasi_pada' => $status === 'dikembalikan' ? now() : null,
        'dikonfirmasi_oleh_id' => $status === 'dikembalikan' ? $admin->id : null,
    ]);
}

test('only admins can access reports', function () {
    $admin = laporanUser('admin');
    $customer = laporanUser('pelanggan');

    $this->get(route('admin.laporan.index'))
        ->assertRedirect(route('login'));

    $this->actingAs($customer)
        ->get(route('admin.laporan.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('admin.laporan.index'))
        ->assertOk()
        ->assertSee('Laporan');
});

test('reports filter reservation statuses payments and confirmed refunds by their respective dates', function () {
    $admin = laporanUser('admin');
    $customer = laporanUser('pelanggan');
    $march = Carbon::parse('2026-03-10 10:00:00');
    $april = Carbon::parse('2026-04-10 10:00:00');

    $selesai = laporanReservasi($customer, 'selesai', $march, 700000);
    laporanPembayaran($selesai, 700000, '2026-03-12');

    $dibatalkan = laporanReservasi($customer, 'dibatalkan', $march, 500000);
    laporanPembayaran($dibatalkan, 500000, '2026-03-13');
    laporanRefund($dibatalkan, $admin, 200000, 'dikembalikan', Carbon::parse('2026-03-14 09:00:00'));

    $outsidePeriod = laporanReservasi($customer, 'disetujui', $april, 300000);
    laporanPembayaran($outsidePeriod, 300000, '2026-04-12');

    $response = $this->actingAs($admin)->get(route('admin.laporan.index', [
        'tanggal_awal' => '2026-03-01',
        'tanggal_akhir' => '2026-03-31',
    ]));

    $response->assertOk()
        ->assertViewHas('totalReservasi', 2)
        ->assertViewHas('totalReservasiSelesai', 1)
        ->assertViewHas('totalReservasiDibatalkan', 1)
        ->assertViewHas('totalPembayaranMasuk', 1200000.0)
        ->assertViewHas('totalRefundDikembalikan', 200000.0)
        ->assertViewHas('pendapatanBersih', 1000000.0)
        ->assertSee('#'.$selesai->id)
        ->assertSee('#'.$dibatalkan->id)
        ->assertDontSee('#'.$outsidePeriod->id);
});

test('a pending refund does not reduce report net revenue', function () {
    $admin = laporanUser('admin');
    $customer = laporanUser('pelanggan');
    $reservasi = laporanReservasi($customer, 'dibatalkan', Carbon::parse('2026-05-10 10:00:00'));
    laporanPembayaran($reservasi, 700000, '2026-05-11');
    laporanRefund($reservasi, $admin, 200000, 'menunggu_pengembalian');

    $this->actingAs($admin)
        ->get(route('admin.laporan.index', [
            'tanggal_awal' => '2026-05-01',
            'tanggal_akhir' => '2026-05-31',
        ]))
        ->assertOk()
        ->assertViewHas('totalPembayaranMasuk', 700000.0)
        ->assertViewHas('totalRefundDikembalikan', 0.0)
        ->assertViewHas('pendapatanBersih', 700000.0);
});

test('report validates the selected date range', function () {
    $admin = laporanUser('admin');

    $this->actingAs($admin)
        ->from(route('admin.laporan.index'))
        ->get(route('admin.laporan.index', [
            'tanggal_awal' => '2026-06-02',
            'tanggal_akhir' => '2026-06-01',
        ]))
        ->assertRedirect(route('admin.laporan.index'))
        ->assertSessionHasErrors('tanggal_awal');
});

test('report paginates reservation details and displays an empty state', function () {
    $admin = laporanUser('admin');
    $customer = laporanUser('pelanggan');

    foreach (range(1, 11) as $number) {
        laporanReservasi($customer, 'menunggu_konfirmasi', Carbon::parse("2026-07-{$number} 10:00:00"));
    }

    $this->actingAs($admin)
        ->get(route('admin.laporan.index'))
        ->assertOk()
        ->assertViewHas('reservasis', fn ($reservasis) => $reservasis->total() === 11 && $reservasis->perPage() === 10);

    $this->actingAs($admin)
        ->get(route('admin.laporan.index', [
            'tanggal_awal' => '2026-08-01',
            'tanggal_akhir' => '2026-08-31',
        ]))
        ->assertOk()
        ->assertSee('Tidak ada reservasi pada periode yang dipilih.')
        ->assertViewHas('totalReservasi', 0);
});
