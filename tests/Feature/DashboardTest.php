<?php

use App\Models\Mobil;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function dashboardUser(string $role): User
{
    $user = User::factory()->create();
    $user->role = $role;
    $user->save();

    return $user;
}

function dashboardMobil(string $plate, string $status): Mobil
{
    return Mobil::query()->create([
        'nama_mobil' => 'Toyota Avanza',
        'merek' => 'Toyota',
        'plat_nomor' => $plate,
        'kapasitas_penumpang' => 7,
        'harga_sewa_per_hari' => 350000,
        'status' => $status,
    ]);
}

function dashboardReservasi(User $customer, Mobil $mobil, string $status, string $createdAt): Reservasi
{
    $reservasi = $customer->reservasis()->create([
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => now()->addDays(2)->toDateString(),
        'tanggal_selesai' => now()->addDays(3)->toDateString(),
        'durasi_sewa' => 2,
        'tarif_harian' => 350000,
        'total_biaya' => 700000,
        'status' => $status,
    ]);

    $reservasi->forceFill([
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ])->saveQuietly();

    return $reservasi->fresh();
}

test('the dashboard shows aggregate database data and recent reservations', function () {
    $admin = dashboardUser('admin');
    $customer = dashboardUser('pelanggan');
    $availableMobil = dashboardMobil('DD 1001 AP', 'tersedia');
    $rentedMobil = dashboardMobil('DD 1002 AP', 'disewa');
    dashboardMobil('DD 1003 AP', 'perawatan');

    dashboardReservasi($customer, $availableMobil, 'menunggu_konfirmasi', now()->subMonths(2)->startOfMonth()->toDateTimeString());
    $latest = dashboardReservasi($customer, $rentedMobil, 'disetujui', now()->toDateTimeString());
    dashboardReservasi($customer, $availableMobil, 'ditolak', now()->subMonth()->toDateTimeString());

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Total Mobil')
        ->assertSee($customer->name)
        ->assertSee($latest->mobil->nama_mobil)
        ->assertViewHas('stats', fn (array $stats): bool => $stats === [
            'totalMobil' => 3,
            'mobilTersedia' => 1,
            'totalReservasi' => 3,
            'menungguKonfirmasi' => 1,
            'disetujui' => 1,
            'ditolak' => 1,
        ])
        ->assertViewHas('recentReservations', fn ($reservasis): bool => $reservasis->first()->is($latest))
        ->assertViewHas('monthlyReservationChart', function (array $chart): bool {
            return count($chart['categories']) === 6
                && count($chart['data']) === 6
                && array_sum($chart['data']) === 3;
        });
});

test('customers cannot access the dashboard', function () {
    $this->actingAs(dashboardUser('pelanggan'))
        ->get(route('dashboard'))
        ->assertForbidden();
});
