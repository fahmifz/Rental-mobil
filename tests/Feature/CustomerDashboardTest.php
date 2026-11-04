<?php

use App\Models\Mobil;
use App\Models\Rating;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function customerDashboardUser(string $role = 'pelanggan'): User
{
    return User::factory()->create(['role' => $role]);
}

function customerDashboardMobil(): Mobil
{
    static $sequence = 0;
    $sequence++;

    return Mobil::query()->create([
        'nama_mobil' => 'Mobil Dashboard '.$sequence,
        'merek' => 'Toyota',
        'plat_nomor' => 'DD 94'.$sequence.' DSH',
        'kapasitas_penumpang' => 5,
        'harga_sewa_per_hari' => 350000,
        'status' => 'tersedia',
    ]);
}

function customerDashboardReservasi(User $customer, string $status, string $start, string $end): Reservasi
{
    return $customer->reservasis()->create([
        'mobil_id' => customerDashboardMobil()->id,
        'tanggal_mulai' => $start,
        'tanggal_selesai' => $end,
        'durasi_sewa' => 2,
        'tarif_harian' => 350000,
        'total_biaya' => 700000,
        'status' => $status,
    ]);
}

test('customer dashboard displays only the logged in customers statistics and recent reservations', function () {
    $customer = customerDashboardUser();
    $active = customerDashboardReservasi($customer, 'disetujui', today()->toDateString(), today()->addDay()->toDateString());
    $completed = customerDashboardReservasi($customer, 'selesai', today()->subDays(5)->toDateString(), today()->subDays(4)->toDateString());
    customerDashboardReservasi($customer, 'dibatalkan', today()->subDays(10)->toDateString(), today()->subDays(9)->toDateString());
    Rating::query()->create([
        'reservasi_id' => $completed->id,
        'user_id' => $customer->id,
        'mobil_id' => $completed->mobil_id,
        'rating' => 5,
    ]);

    $otherCustomer = customerDashboardUser();
    $otherReservation = customerDashboardReservasi($otherCustomer, 'disetujui', today()->toDateString(), today()->addDay()->toDateString());

    $this->actingAs($customer)
        ->get(route('account'))
        ->assertOk()
        ->assertSee('Dashboard Penyewa')
        ->assertSee($customer->name)
        ->assertSee('Total Reservasi')
        ->assertSee('Penyewaan Aktif')
        ->assertSee('Rating Diberikan')
        ->assertSee(route('pelanggan.rekomendasi.index'), false)
        ->assertSee(route('reservasi.index'), false)
        ->assertViewHas('statistics', fn (array $statistics) => $statistics === [
            'total_reservasi' => 3,
            'penyewaan_aktif' => 1,
            'rating_diberikan' => 1,
        ])
        ->assertViewHas('recentReservations', fn ($reservations) => $reservations->every(
            fn (Reservasi $reservasi) => $reservasi->user_id === $customer->id
        ))
        ->assertDontSee($otherReservation->mobil->nama_mobil);

    expect($active->user_id)->toBe($customer->id);
});

test('customer dashboard is restricted to customers and keeps customer navigation available', function () {
    $customer = customerDashboardUser();
    $admin = customerDashboardUser('admin');

    $this->get(route('account'))
        ->assertRedirect(route('login'));

    $this->actingAs($admin)
        ->get(route('account'))
        ->assertForbidden();

    $this->actingAs($customer)
        ->get(route('account'))
        ->assertOk()
        ->assertSee(route('reservasi.create'), false)
        ->assertSee(route('reservasi.index'), false)
        ->assertSee(route('pelanggan.rekomendasi.index'), false);

    $this->actingAs($customer)
        ->get(route('reservasi.index'))
        ->assertOk();
});

test('public desktop and mobile navigation expose recommendations only to customers', function () {
    $customer = customerDashboardUser();
    $admin = customerDashboardUser('admin');

    $this->actingAs($customer)
        ->get(route('home'))
        ->assertOk()
        ->assertSee(route('pelanggan.rekomendasi.index'), false)
        ->assertSee('Rekomendasi Mobil');

    $this->actingAs($admin)
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee(route('pelanggan.rekomendasi.index'), false);
});
