<?php

use App\Models\Mobil;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function reservasiMobil(array $overrides = []): Mobil
{
    return Mobil::query()->create(array_merge([
        'nama_mobil' => 'Toyota Avanza',
        'merek' => 'Toyota',
        'plat_nomor' => 'DD 1234 AB',
        'kapasitas_penumpang' => 7,
        'harga_sewa_per_hari' => 350000,
        'status' => 'tersedia',
    ], $overrides));
}

function reservasiPelanggan(): User
{
    $user = User::factory()->create();
    $user->role = 'pelanggan';
    $user->save();

    return $user;
}

test('a customer can create a reservation using the database rate and own account', function () {
    $customer = reservasiPelanggan();
    $otherCustomer = reservasiPelanggan();
    $mobil = reservasiMobil(['harga_sewa_per_hari' => 350000]);

    $response = $this->actingAs($customer)
        ->post(route('reservasi.store'), [
            'user_id' => $otherCustomer->id,
            'mobil_id' => $mobil->id,
            'tanggal_mulai' => today()->addDays(2)->toDateString(),
            'tanggal_selesai' => today()->addDays(4)->toDateString(),
            'tarif_harian' => 1,
            'total_biaya' => 1,
            'durasi_sewa' => 999,
            'status' => 'disetujui',
        ]);

    $reservasi = Reservasi::query()->firstOrFail();

    $response->assertRedirect(route('reservasi.show', $reservasi));

    expect($reservasi->user_id)->toBe($customer->id)
        ->and($reservasi->mobil_id)->toBe($mobil->id)
        ->and($reservasi->durasi_sewa)->toBe(3)
        ->and((float) $reservasi->tarif_harian)->toBe(350000.0)
        ->and((float) $reservasi->total_biaya)->toBe(1050000.0)
        ->and($reservasi->status)->toBe('menunggu_konfirmasi')
        ->and($mobil->fresh()->status)->toBe('tersedia');
});

test('a customer can view the reservation form with available cars and database rates', function () {
    $customer = reservasiPelanggan();
    reservasiMobil(['harga_sewa_per_hari' => 275000]);

    $this->actingAs($customer)
        ->get(route('reservasi.create'))
        ->assertOk()
        ->assertSee('Buat Reservasi')
        ->assertSee('Toyota Avanza')
        ->assertSee('data-rate="275000.00"', false);
});

test('reservation dates cannot start in the past or end before they start', function () {
    $customer = reservasiPelanggan();
    $mobil = reservasiMobil();

    $this->actingAs($customer)
        ->from(route('reservasi.create'))
        ->post(route('reservasi.store'), [
            'mobil_id' => $mobil->id,
            'tanggal_mulai' => today()->subDay()->toDateString(),
            'tanggal_selesai' => today()->subDay()->toDateString(),
        ])
        ->assertRedirect(route('reservasi.create'))
        ->assertSessionHasErrors('tanggal_mulai');

    $this->actingAs($customer)
        ->from(route('reservasi.create'))
        ->post(route('reservasi.store'), [
            'mobil_id' => $mobil->id,
            'tanggal_mulai' => today()->addDays(2)->toDateString(),
            'tanggal_selesai' => today()->addDay()->toDateString(),
        ])
        ->assertRedirect(route('reservasi.create'))
        ->assertSessionHasErrors('tanggal_selesai');

    expect(Reservasi::query()->count())->toBe(0);
});

test('overlapping pending or approved reservations are rejected', function () {
    $customer = reservasiPelanggan();
    $mobil = reservasiMobil();
    $start = today()->addDays(2);
    $end = today()->addDays(4);

    $customer->reservasis()->create([
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => $start,
        'tanggal_selesai' => $end,
        'durasi_sewa' => 3,
        'tarif_harian' => 350000,
        'total_biaya' => 1050000,
        'status' => 'disetujui',
    ]);

    $this->actingAs(reservasiPelanggan())
        ->from(route('reservasi.create'))
        ->post(route('reservasi.store'), [
            'mobil_id' => $mobil->id,
            'tanggal_mulai' => $end->toDateString(),
            'tanggal_selesai' => $end->addDay()->toDateString(),
        ])
        ->assertRedirect(route('reservasi.create'))
        ->assertSessionHasErrors('tanggal_mulai');

    expect(Reservasi::query()->count())->toBe(1);
});

test('customers only see their own reservations and cannot view another customer reservation', function () {
    $customer = reservasiPelanggan();
    $otherCustomer = reservasiPelanggan();
    $mobil = reservasiMobil();
    $ownReservation = $customer->reservasis()->create([
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => today()->addDays(2),
        'tanggal_selesai' => today()->addDays(2),
        'durasi_sewa' => 1,
        'tarif_harian' => 350000,
        'total_biaya' => 350000,
        'status' => 'menunggu_konfirmasi',
    ]);
    $otherReservation = $otherCustomer->reservasis()->create([
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => today()->addDays(3),
        'tanggal_selesai' => today()->addDays(3),
        'durasi_sewa' => 1,
        'tarif_harian' => 350000,
        'total_biaya' => 350000,
        'status' => 'menunggu_konfirmasi',
    ]);

    $this->actingAs($customer)
        ->get(route('reservasi.index'))
        ->assertOk()
        ->assertSee('Toyota Avanza')
        ->assertSee(route('reservasi.show', $ownReservation))
        ->assertDontSee(route('reservasi.show', $otherReservation));

    $this->actingAs($customer)
        ->get(route('reservasi.show', $ownReservation))
        ->assertOk()
        ->assertSee('Detail Reservasi')
        ->assertSee('Toyota Avanza');

    $this->actingAs($customer)
        ->get(route('reservasi.show', $otherReservation))
        ->assertNotFound();
});

test('only customers can access customer reservation routes', function () {
    $this->get(route('reservasi.index'))
        ->assertRedirect(route('login'));

    $admin = User::factory()->create();
    $admin->role = 'admin';
    $admin->save();

    $this->actingAs($admin)
        ->get(route('reservasi.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('reservasi.store'), [])
        ->assertForbidden();
});
