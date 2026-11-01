<?php

use App\Models\Mobil;
use App\Models\Pengembalian;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function pengembalianUser(string $role): User
{
    $user = User::factory()->create();
    $user->role = $role;
    $user->save();

    return $user;
}

function pengembalianMobil(string $plate = 'DD 9001 RET', string $status = 'disewa'): Mobil
{
    return Mobil::query()->create([
        'nama_mobil' => 'Innova Reborn',
        'merek' => 'Toyota',
        'plat_nomor' => $plate,
        'kapasitas_penumpang' => 7,
        'harga_sewa_per_hari' => 500000,
        'status' => $status,
    ]);
}

function pengembalianReservasi(
    User $customer,
    Mobil $mobil,
    string $startDate,
    string $endDate,
    string $status = 'disetujui'
): Reservasi {
    return $customer->reservasis()->create([
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => $startDate,
        'tanggal_selesai' => $endDate,
        'durasi_sewa' => 3,
        'tarif_harian' => 500000,
        'total_biaya' => 1500000,
        'status' => $status,
    ]);
}

test('an admin can view the return management page with waiting reservations', function () {
    $admin = pengembalianUser('admin');
    $customer = pengembalianUser('pelanggan');
    $mobil = pengembalianMobil();
    $reservasi = pengembalianReservasi(
        $customer,
        $mobil,
        today()->subDays(3)->toDateString(),
        today()->toDateString()
    );

    $this->actingAs($admin)
        ->get(route('admin.pengembalian.index'))
        ->assertOk()
        ->assertSee('Pengembalian Unit Mobil')
        ->assertSee($customer->name)
        ->assertSee($mobil->nama_mobil)
        ->assertSee($mobil->plat_nomor)
        ->assertSee(route('admin.pengembalian.create', $reservasi));
});

test('a valid approved reservation can be returned on time without late penalty', function () {
    $admin = pengembalianUser('admin');
    $customer = pengembalianUser('pelanggan');
    $mobil = pengembalianMobil('DD 9002 RET', 'disewa');
    $reservasi = pengembalianReservasi(
        $customer,
        $mobil,
        today()->subDays(2)->toDateString(),
        today()->toDateString(),
        'disetujui'
    );

    $returnTime = today()->setTime(15, 30)->toDateTimeString();

    $response = $this->actingAs($admin)
        ->post(route('admin.pengembalian.store', $reservasi), [
            'tanggal_pengembalian' => $returnTime,
            'kondisi_mobil' => 'baik',
            'catatan_kondisi' => 'Kondisi mulus, bbm penuh.',
            'status_mobil_setelah_kembali' => 'tersedia',
        ]);

    $response->assertRedirect(route('admin.pengembalian.show', $reservasi))
        ->assertSessionHas('success', 'Pengembalian mobil berhasil dicatat.');

    $this->assertDatabaseHas('pengembalians', [
        'reservasi_id' => $reservasi->id,
        'kondisi_mobil' => 'baik',
        'durasi_keterlambatan' => 0,
        'total_denda' => 0,
        'status_mobil_setelah_kembali' => 'tersedia',
        'diproses_oleh_id' => $admin->id,
    ]);

    expect($reservasi->fresh()->status)->toBe('selesai')
        ->and($mobil->fresh()->status)->toBe('tersedia');
});

test('a late return calculates delay rounded up and exact penalty', function () {
    config(['rental.tarif_denda_per_hari' => 50000.0]);

    $admin = pengembalianUser('admin');
    $customer = pengembalianUser('pelanggan');
    $mobil = pengembalianMobil('DD 9003 RET', 'disewa');

    // Deadline was 2026-10-31 23:59:59
    $reservasi = pengembalianReservasi(
        $customer,
        $mobil,
        '2026-10-28',
        '2026-10-31',
        'disetujui'
    );

    // Returned on 2026-11-01 10:00:00 -> 10 hours overdue past end-of-day deadline -> 1 day rounded up
    $returnTime = '2026-11-01 10:00:00';

    $this->actingAs($admin)
        ->post(route('admin.pengembalian.store', $reservasi), [
            'tanggal_pengembalian' => $returnTime,
            'kondisi_mobil' => 'baik',
            'status_mobil_setelah_kembali' => 'tersedia',
        ])
        ->assertRedirect(route('admin.pengembalian.show', $reservasi))
        ->assertSessionHas('success');

    $pengembalian = Pengembalian::query()->where('reservasi_id', $reservasi->id)->firstOrFail();

    expect($pengembalian->durasi_keterlambatan)->toBe(1)
        ->and((float) $pengembalian->tarif_denda_per_hari)->toBe(50000.0)
        ->and((float) $pengembalian->total_denda)->toBe(50000.0)
        ->and($pengembalian->isLate())->toBeTrue();
});

test('a late return of 25 hours past deadline rounds up to 2 days penalty', function () {
    config(['rental.tarif_denda_per_hari' => 75000.0]);

    $admin = pengembalianUser('admin');
    $customer = pengembalianUser('pelanggan');
    $mobil = pengembalianMobil('DD 9004 RET', 'disewa');

    $reservasi = pengembalianReservasi(
        $customer,
        $mobil,
        '2026-10-25',
        '2026-10-28',
        'disetujui'
    );

    // Deadline: 2026-10-28 23:59:59
    // Returned: 2026-10-30 01:00:00 (25 hours after deadline) -> 2 days rounded up
    $returnTime = '2026-10-30 01:00:00';

    $this->actingAs($admin)
        ->post(route('admin.pengembalian.store', $reservasi), [
            'tanggal_pengembalian' => $returnTime,
            'kondisi_mobil' => 'rusak_ringan',
            'catatan_kondisi' => 'Baret halus bumper depan.',
            'status_mobil_setelah_kembali' => 'tersedia',
        ])
        ->assertRedirect(route('admin.pengembalian.show', $reservasi));

    $pengembalian = Pengembalian::query()->where('reservasi_id', $reservasi->id)->firstOrFail();

    expect($pengembalian->durasi_keterlambatan)->toBe(2)
        ->and((float) $pengembalian->tarif_denda_per_hari)->toBe(75000.0)
        ->and((float) $pengembalian->total_denda)->toBe(150000.0);
});

test('reservations that are completed, rejected, or cancelled cannot be returned', function () {
    $admin = pengembalianUser('admin');
    $customer = pengembalianUser('pelanggan');

    foreach (['selesai', 'ditolak', 'dibatalkan', 'menunggu_konfirmasi'] as $status) {
        $mobil = pengembalianMobil("DD 900{$status} RET", 'tersedia');
        $reservasi = pengembalianReservasi(
            $customer,
            $mobil,
            today()->subDays(2)->toDateString(),
            today()->toDateString(),
            $status
        );

        $this->actingAs($admin)
            ->post(route('admin.pengembalian.store', $reservasi), [
                'tanggal_pengembalian' => now()->toDateTimeString(),
                'kondisi_mobil' => 'baik',
                'status_mobil_setelah_kembali' => 'tersedia',
            ])
            ->assertSessionHasErrors('reservasi');

        expect(Pengembalian::query()->where('reservasi_id', $reservasi->id)->count())->toBe(0);
    }
});

test('a reservation cannot be returned more than once', function () {
    $admin = pengembalianUser('admin');
    $customer = pengembalianUser('pelanggan');
    $mobil = pengembalianMobil('DD 9005 RET', 'disewa');
    $reservasi = pengembalianReservasi(
        $customer,
        $mobil,
        today()->subDays(2)->toDateString(),
        today()->toDateString(),
        'disetujui'
    );

    // First return: succeeds
    $this->actingAs($admin)
        ->post(route('admin.pengembalian.store', $reservasi), [
            'tanggal_pengembalian' => now()->toDateTimeString(),
            'kondisi_mobil' => 'baik',
            'status_mobil_setelah_kembali' => 'tersedia',
        ])
        ->assertRedirect(route('admin.pengembalian.show', $reservasi));

    // Second return: rejected
    $this->actingAs($admin)
        ->post(route('admin.pengembalian.store', $reservasi), [
            'tanggal_pengembalian' => now()->toDateTimeString(),
            'kondisi_mobil' => 'baik',
            'status_mobil_setelah_kembali' => 'tersedia',
        ])
        ->assertSessionHasErrors('reservasi');

    expect(Pengembalian::query()->where('reservasi_id', $reservasi->id)->count())->toBe(1);
});

test('admin can choose car status as maintenance and it is preserved', function () {
    $admin = pengembalianUser('admin');
    $customer = pengembalianUser('pelanggan');
    $mobil = pengembalianMobil('DD 9006 RET', 'disewa');
    $reservasi = pengembalianReservasi(
        $customer,
        $mobil,
        today()->subDays(2)->toDateString(),
        today()->toDateString(),
        'disetujui'
    );

    $this->actingAs($admin)
        ->post(route('admin.pengembalian.store', $reservasi), [
            'tanggal_pengembalian' => now()->toDateTimeString(),
            'kondisi_mobil' => 'rusak_berat',
            'catatan_kondisi' => 'Kopling slip dan radiator bocor.',
            'status_mobil_setelah_kembali' => 'perawatan',
        ])
        ->assertRedirect(route('admin.pengembalian.show', $reservasi));

    expect($mobil->fresh()->status)->toBe('perawatan');
});

test('customers and guests cannot access admin return endpoints', function () {
    $customer = pengembalianUser('pelanggan');
    $mobil = pengembalianMobil('DD 9007 RET');
    $reservasi = pengembalianReservasi(
        $customer,
        $mobil,
        today()->subDays(2)->toDateString(),
        today()->toDateString(),
        'disetujui'
    );

    // Guest redirected to login
    $this->get(route('admin.pengembalian.index'))
        ->assertRedirect(route('login'));

    $this->post(route('admin.pengembalian.store', $reservasi), [])
        ->assertRedirect(route('login'));

    // Customer forbidden (403)
    $this->actingAs($customer)
        ->get(route('admin.pengembalian.index'))
        ->assertForbidden();

    $this->actingAs($customer)
        ->get(route('admin.pengembalian.create', $reservasi))
        ->assertForbidden();

    $this->actingAs($customer)
        ->post(route('admin.pengembalian.store', $reservasi), [])
        ->assertForbidden();
});

test('a return date in the future is rejected with a validation error', function () {
    $admin = pengembalianUser('admin');
    $customer = pengembalianUser('pelanggan');
    $mobil = pengembalianMobil('DD 9009 RET', 'disewa');
    $reservasi = pengembalianReservasi(
        $customer,
        $mobil,
        today()->subDays(2)->toDateString(),
        today()->toDateString(),
        'disetujui'
    );

    $futureReturnTime = now()->addDay()->toDateTimeString();

    $this->actingAs($admin)
        ->post(route('admin.pengembalian.store', $reservasi), [
            'tanggal_pengembalian' => $futureReturnTime,
            'kondisi_mobil' => 'baik',
            'status_mobil_setelah_kembali' => 'tersedia',
        ])
        ->assertSessionHasErrors('tanggal_pengembalian');

    expect(Pengembalian::query()->where('reservasi_id', $reservasi->id)->count())->toBe(0);
});

test('an admin can view return detail page with planned schedule, actual return, and penalty details', function () {
    $admin = pengembalianUser('admin');
    $customer = pengembalianUser('pelanggan');
    $mobil = pengembalianMobil('DD 9008 RET', 'disewa');
    $reservasi = pengembalianReservasi(
        $customer,
        $mobil,
        '2026-10-25',
        '2026-10-28',
        'disetujui'
    );

    $actualReturn = Carbon::parse('2026-10-30 11:30:00');

    $this->actingAs($admin)
        ->post(route('admin.pengembalian.store', $reservasi), [
            'tanggal_pengembalian' => $actualReturn->toDateTimeString(),
            'kondisi_mobil' => 'rusak_ringan',
            'catatan_kondisi' => 'Baret spion kiri.',
            'status_mobil_setelah_kembali' => 'tersedia',
        ]);

    $this->actingAs($admin)
        ->get(route('admin.pengembalian.show', $reservasi))
        ->assertOk()
        ->assertSee('Detail Pengembalian Mobil')
        ->assertSee('Innova Reborn')
        ->assertSee('DD 9008 RET')
        ->assertSee($reservasi->tanggal_selesai->format('d M Y') . ', 23:59 WITA')
        ->assertSee($actualReturn->format('d M Y, H:i') . ' WITA')
        ->assertSee('Terlambat 2 Hari')
        ->assertSee('Baret spion kiri.')
        ->assertSee('100.000');
});
