<?php

use App\Models\Mobil;
use App\Models\Pembayaran;
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

test('approving a reservation active today marks the car as rented', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 6788 AB');
    $reservasi = createAdminTestReservation(
        $customer,
        $mobil,
        today()->toDateString(),
        today()->addDays(2)->toDateString(),
    );

    $this->actingAs($admin)
        ->post(route('admin.reservasi.approve', $reservasi))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHas('success', 'Reservasi berhasil disetujui.');

    expect($mobil->fresh()->status)->toBe('disewa');
});

test('an admin cannot approve a reservation for a car under maintenance', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 6787 AB');
    $mobil->update(['status' => 'perawatan']);
    $reservasi = createAdminTestReservation(
        $customer,
        $mobil,
        today()->toDateString(),
        today()->addDays(2)->toDateString(),
    );

    $this->actingAs($admin)
        ->from(route('admin.reservasi.index'))
        ->post(route('admin.reservasi.approve', $reservasi))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHasErrors('reservasi');

    expect($mobil->fresh()->status)->toBe('perawatan');

    $this->assertDatabaseHas('reservasis', [
        'id' => $reservasi->id,
        'status' => 'menunggu_konfirmasi',
    ]);
});

test('an admin cannot approve a reservation whose end date has passed', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 6786 AB');
    $reservasi = createAdminTestReservation(
        $customer,
        $mobil,
        today()->subDays(3)->toDateString(),
        today()->subDay()->toDateString(),
    );

    $this->actingAs($admin)
        ->from(route('admin.reservasi.index'))
        ->post(route('admin.reservasi.approve', $reservasi))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHasErrors('reservasi');

    $this->assertDatabaseHas('reservasis', [
        'id' => $reservasi->id,
        'status' => 'menunggu_konfirmasi',
    ]);
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

test('approved reservations direct admins to return processing', function () {
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
        ->get(route('admin.reservasi.index'))
        ->assertOk()
        ->assertSee('Proses Pengembalian')
        ->assertSee(route('admin.pengembalian.create', $reservasi))
        ->assertDontSee('Selesaikan');
});

test('an approved reservation cannot be completed without a saved return', function () {
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
        ->post("/admin/reservasi/{$reservasi->id}/selesaikan")
        ->assertNotFound();

    $this->assertDatabaseHas('reservasis', [
        'id' => $reservasi->id,
        'status' => 'disetujui',
    ]);
    $this->assertDatabaseMissing('pengembalians', [
        'reservasi_id' => $reservasi->id,
    ]);

    expect(\Illuminate\Support\Facades\Route::has('admin.reservasi.complete'))
        ->toBeFalse();
});

test('an admin can cancel a pending reservation', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 7001 CAN');
    $reservasi = createAdminTestReservation(
        $customer,
        $mobil,
        today()->addDays(2)->toDateString(),
        today()->addDays(4)->toDateString(),
        'menunggu_konfirmasi',
    );

    $this->actingAs($admin)
        ->post(route('admin.reservasi.cancel', $reservasi))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHas('success', 'Reservasi berhasil dibatalkan.');

    $this->assertDatabaseHas('reservasis', [
        'id' => $reservasi->id,
        'status' => 'dibatalkan',
    ]);
    expect($mobil->fresh()->status)->toBe('tersedia');
});

test('an admin can cancel an approved reservation before its start date', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 7002 CAN');
    $reservasi = createAdminTestReservation(
        $customer,
        $mobil,
        today()->addDays(2)->toDateString(),
        today()->addDays(4)->toDateString(),
        'disetujui',
    );

    $this->actingAs($admin)
        ->post(route('admin.reservasi.cancel', $reservasi))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHas('success', 'Reservasi berhasil dibatalkan.');

    expect($reservasi->fresh()->status)->toBe('dibatalkan')
        ->and($mobil->fresh()->status)->toBe('tersedia');
});

test('an admin can cancel an approved reservation active today and car operational status syncs to available', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 7003 CAN');
    $mobil->update(['status' => 'disewa']);
    $reservasi = createAdminTestReservation(
        $customer,
        $mobil,
        today()->toDateString(),
        today()->addDays(2)->toDateString(),
        'disetujui',
    );

    $this->actingAs($admin)
        ->post(route('admin.reservasi.cancel', $reservasi))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHas('success', 'Reservasi berhasil dibatalkan.');

    expect($reservasi->fresh()->status)->toBe('dibatalkan')
        ->and($mobil->fresh()->status)->toBe('tersedia');
});

test('cancelling an approved reservation keeps the car rented if another approved reservation is active today', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 7004 CAN');
    $mobil->update(['status' => 'disewa']);
    $cancelled = createAdminTestReservation(
        $customer,
        $mobil,
        today()->subDays(1)->toDateString(),
        today()->toDateString(),
        'disetujui',
    );
    createAdminTestReservation(
        $customer,
        $mobil,
        today()->toDateString(),
        today()->addDays(2)->toDateString(),
        'disetujui',
    );

    $this->actingAs($admin)
        ->post(route('admin.reservasi.cancel', $cancelled))
        ->assertRedirect(route('admin.reservasi.index'));

    expect($cancelled->fresh()->status)->toBe('dibatalkan')
        ->and($mobil->fresh()->status)->toBe('disewa');
});

test('cancelling an approved reservation preserves maintenance status of the car', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 7005 CAN');
    $mobil->update(['status' => 'perawatan']);
    $reservasi = createAdminTestReservation(
        $customer,
        $mobil,
        today()->toDateString(),
        today()->addDays(2)->toDateString(),
        'disetujui',
    );

    $this->actingAs($admin)
        ->post(route('admin.reservasi.cancel', $reservasi))
        ->assertRedirect(route('admin.reservasi.index'));

    expect($reservasi->fresh()->status)->toBe('dibatalkan')
        ->and($mobil->fresh()->status)->toBe('perawatan');
});

test('an admin can cancel an approved reservation after start date has passed', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 7006 CAN');
    $reservasi = createAdminTestReservation(
        $customer,
        $mobil,
        today()->subDays(3)->toDateString(),
        today()->addDays(2)->toDateString(),
        'disetujui',
    );

    $this->actingAs($admin)
        ->post(route('admin.reservasi.cancel', $reservasi))
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHas('success', 'Reservasi berhasil dibatalkan.');

    expect($reservasi->fresh()->status)->toBe('dibatalkan');
});

test('an admin cannot cancel completed, rejected, or already cancelled reservations', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');

    foreach (['selesai', 'ditolak', 'dibatalkan'] as $index => $status) {
        $mobil = adminReservasiMobil("DD 700{$index} ERR");
        $reservasi = createAdminTestReservation(
            $customer,
            $mobil,
            today()->addDays(2)->toDateString(),
            today()->addDays(4)->toDateString(),
            $status,
        );

        $this->actingAs($admin)
            ->from(route('admin.reservasi.index'))
            ->post(route('admin.reservasi.cancel', $reservasi))
            ->assertRedirect(route('admin.reservasi.index'))
            ->assertSessionHasErrors('reservasi');

        expect($reservasi->fresh()->status)->toBe($status);
    }
});

test('cancelling an admin reservation with recorded payments preserves payment records untouched', function () {
    $admin = adminReservasiUser('admin');
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 7007 PAY');
    $reservasi = createAdminTestReservation(
        $customer,
        $mobil,
        today()->toDateString(),
        today()->addDays(2)->toDateString(),
        'disetujui',
    );

    $payment = Pembayaran::query()->create([
        'reservasi_id' => $reservasi->id,
        'nominal_pembayaran' => 350000,
        'tanggal_pembayaran' => today()->toDateString(),
        'metode_pembayaran' => 'transfer',
        'status' => 'dibayar_sebagian',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.reservasi.cancel', $reservasi), [
            'jenis_refund' => 'penuh',
            'nominal_refund' => 350000,
            'alasan_refund' => 'Pembayaran dikembalikan penuh.',
        ])
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHas('success', 'Reservasi berhasil dibatalkan.');

    expect($reservasi->fresh()->status)->toBe('dibatalkan');
    $this->assertDatabaseHas('pembayarans', [
        'id' => $payment->id,
        'reservasi_id' => $reservasi->id,
        'nominal_pembayaran' => 350000,
        'metode_pembayaran' => 'transfer',
        'status' => 'dibayar_sebagian',
    ]);
    $this->assertDatabaseHas('refunds', [
        'reservasi_id' => $reservasi->id,
        'jenis_refund' => 'penuh',
        'nominal_refund' => 350000,
        'status' => 'menunggu_pengembalian',
    ]);
});

test('customers cannot access admin cancel endpoint', function () {
    $customer = adminReservasiUser('pelanggan');
    $mobil = adminReservasiMobil('DD 7008 FORB');
    $reservasi = createAdminTestReservation(
        $customer,
        $mobil,
        today()->addDays(2)->toDateString(),
        today()->addDays(4)->toDateString(),
    );

    $this->actingAs($customer)
        ->post(route('admin.reservasi.cancel', $reservasi))
        ->assertForbidden();

    expect($reservasi->fresh()->status)->toBe('menunggu_konfirmasi');
});
