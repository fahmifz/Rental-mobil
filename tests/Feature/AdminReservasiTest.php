<?php

use App\Models\Mobil;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function adminReservasiUser(string $role): User
{
    $user = User::factory()->create();
    $user->role = $role;
    $user->save();

    return $user;
}

function adminReservasiMobil(string $plate): Mobil
{
    return Mobil::query()->create([
        'nama_mobil' => 'Toyota Avanza',
        'merek' => 'Toyota',
        'plat_nomor' => $plate,
        'kapasitas_penumpang' => 7,
        'harga_sewa_per_hari' => 350000,
        'status' => 'tersedia',
    ]);
}

function createAdminTestReservation(User $customer, Mobil $mobil, string $start, string $end, string $status = 'menunggu_konfirmasi'): Reservasi
{
    return $customer->reservasis()->create([
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => $start,
        'tanggal_selesai' => $end,
        'durasi_sewa' => 2,
        'tarif_harian' => 350000,
        'total_biaya' => 700000,
        'status' => $status,
    ]);
}

test('an admin can view all reservations from the dashboard menu route', function () {
    $admin = User::factory()->create();
    $admin->role = 'admin';
    $admin->save();
    $customer = User::factory()->create();
    $customer->role = 'pelanggan';
    $customer->save();
    $mobil = Mobil::query()->create([
        'nama_mobil' => 'Toyota Avanza',
        'merek' => 'Toyota',
        'plat_nomor' => 'DD 5678 AB',
        'kapasitas_penumpang' => 7,
        'harga_sewa_per_hari' => 350000,
        'status' => 'tersedia',
    ]);
    $reservasi = $customer->reservasis()->create([
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => today()->addDays(2),
        'tanggal_selesai' => today()->addDays(3),
        'durasi_sewa' => 2,
        'tarif_harian' => 350000,
        'total_biaya' => 700000,
        'status' => 'menunggu_konfirmasi',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reservasi.index'))
        ->assertOk()
        ->assertSee($customer->name)
        ->assertSee($customer->email)
        ->assertSee($mobil->nama_mobil)
        ->assertSee('700.000')
        ->assertSee('Menunggu Konfirmasi')
        ->assertSee(route('admin.reservasi.approve', $reservasi))
        ->assertSee(route('admin.reservasi.reject', $reservasi));
});

test('customers cannot access the admin reservation list', function () {
    $customer = User::factory()->create();
    $customer->role = 'pelanggan';
    $customer->save();

    $this->actingAs($customer)
        ->get(route('admin.reservasi.index'))
        ->assertForbidden();
});

test('an admin can approve a pending reservation without changing car status', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 6789 AB');
    $reservasi = createAdminTestReservation(
        $customer,
        $mobil,
        today()->addDays(2)->toDateString(),
        today()->addDays(3)->toDateString(),
    );

    $this->actingAs($admin)
        ->post(route('admin.reservasi.approve', $reservasi))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHas('success', 'Reservasi berhasil disetujui.');

    $this->assertDatabaseHas('reservasis', [
        'id' => $reservasi->id,
        'status' => 'disetujui',
    ]);
    expect($mobil->fresh()->status)->toBe('tersedia');

    $this->actingAs($customer)
        ->get(route('reservasi.index'))
        ->assertOk()
        ->assertSee('Disetujui');
});

test('an admin can reject a pending reservation', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 6790 AB');
    $reservasi = createAdminTestReservation(
        $customer,
        $mobil,
        today()->addDays(2)->toDateString(),
        today()->addDays(3)->toDateString(),
    );

    $this->actingAs($admin)
        ->post(route('admin.reservasi.reject', $reservasi))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHas('success', 'Reservasi berhasil ditolak.');

    $this->assertDatabaseHas('reservasis', [
        'id' => $reservasi->id,
        'status' => 'ditolak',
    ]);
});

test('a processed reservation cannot be processed again', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 6791 AB');
    $reservasi = createAdminTestReservation(
        $customer,
        $mobil,
        today()->addDays(2)->toDateString(),
        today()->addDays(3)->toDateString(),
        'disetujui',
    );

    $this->actingAs($admin)
        ->from(route('admin.reservasi.index'))
        ->post(route('admin.reservasi.reject', $reservasi))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHasErrors('reservasi');

    $this->assertDatabaseHas('reservasis', [
        'id' => $reservasi->id,
        'status' => 'disetujui',
    ]);
});

test('customers cannot access admin reservation status endpoints', function () {
    $customer = adminReservasiUser('pelanggan');
    $owner = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 6792 AB');
    $reservasi = createAdminTestReservation(
        $owner,
        $mobil,
        today()->addDays(2)->toDateString(),
        today()->addDays(3)->toDateString(),
    );

    $this->actingAs($customer)
        ->post(route('admin.reservasi.approve', $reservasi))
        ->assertForbidden();
    $this->actingAs($customer)
        ->post(route('admin.reservasi.reject', $reservasi))
        ->assertForbidden();
    $this->actingAs($customer)
        ->post(route('admin.reservasi.complete', $reservasi))
        ->assertForbidden();

    $this->assertDatabaseHas('reservasis', [
        'id' => $reservasi->id,
        'status' => 'menunggu_konfirmasi',
    ]);
});

test('an admin cannot approve a reservation that conflicts with a pending or approved reservation', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $start = today()->addDays(2)->toDateString();
    $end = today()->addDays(3)->toDateString();

    foreach (['menunggu_konfirmasi', 'disetujui'] as $index => $conflictingStatus) {
        $mobil = adminReservasiMobil('DD 679'.($index + 3).' AB');
        $target = createAdminTestReservation($customer, $mobil, $start, $end);
        createAdminTestReservation($customer, $mobil, $start, $end, $conflictingStatus);

        $this->actingAs($admin)
            ->from(route('admin.reservasi.index'))
            ->post(route('admin.reservasi.approve', $target))
            ->assertRedirect(route('admin.reservasi.index'))
            ->assertSessionHasErrors('reservasi');

        $this->assertDatabaseHas('reservasis', [
            'id' => $target->id,
            'status' => 'menunggu_konfirmasi',
        ]);
    }
});

test('a reservation ending on another reservation start date is considered a conflict', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 6795 AB');
    $sharedDate = today()->addDays(4)->toDateString();
    $target = createAdminTestReservation(
        $customer,
        $mobil,
        today()->addDays(2)->toDateString(),
        $sharedDate,
    );
    createAdminTestReservation(
        $customer,
        $mobil,
        $sharedDate,
        today()->addDays(5)->toDateString(),
        'disetujui',
    );

    $this->actingAs($admin)
        ->from(route('admin.reservasi.index'))
        ->post(route('admin.reservasi.approve', $target))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHasErrors('reservasi');

    $this->assertDatabaseHas('reservasis', [
        'id' => $target->id,
        'status' => 'menunggu_konfirmasi',
    ]);
});

test('an admin can complete an approved reservation on its end date', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 6796 AB');
    $reservasi = createAdminTestReservation(
        $customer,
        $mobil,
        today()->subDays(2)->toDateString(),
        today()->toDateString(),
        'disetujui',
    );

    $this->actingAs($admin)
        ->post(route('admin.reservasi.complete', $reservasi))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHas('success', 'Reservasi berhasil diselesaikan.');

    $this->assertDatabaseHas('reservasis', [
        'id' => $reservasi->id,
        'status' => 'selesai',
    ]);
});

test('an approved reservation cannot be completed before its end date', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 6797 AB');
    $reservasi = createAdminTestReservation(
        $customer,
        $mobil,
        today()->toDateString(),
        today()->addDay()->toDateString(),
        'disetujui',
    );

    $this->actingAs($admin)
        ->from(route('admin.reservasi.index'))
        ->post(route('admin.reservasi.complete', $reservasi))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHasErrors('reservasi');

    $this->assertDatabaseHas('reservasis', [
        'id' => $reservasi->id,
        'status' => 'disetujui',
    ]);
});

test('only approved reservations can be completed', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 6798 AB');
    $reservasi = createAdminTestReservation(
        $customer,
        $mobil,
        today()->subDays(2)->toDateString(),
        today()->toDateString(),
    );

    $this->actingAs($admin)
        ->from(route('admin.reservasi.index'))
        ->post(route('admin.reservasi.complete', $reservasi))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHasErrors('reservasi');

    $this->assertDatabaseHas('reservasis', [
        'id' => $reservasi->id,
        'status' => 'menunggu_konfirmasi',
    ]);
});
