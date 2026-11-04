<?php

use App\Models\Mobil;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function adminPelangganUser(string $role, array $attributes = []): User
{
    return User::factory()->create(array_merge(['role' => $role], $attributes));
}

function adminPelangganMobil(): Mobil
{
    static $sequence = 0;
    $sequence++;

    return Mobil::query()->create([
        'nama_mobil' => 'Mobil Pelanggan '.$sequence,
        'merek' => 'Toyota',
        'plat_nomor' => 'DD 98'.$sequence.' PLG',
        'kapasitas_penumpang' => 7,
        'harga_sewa_per_hari' => 350000,
        'status' => 'tersedia',
    ]);
}

function adminPelangganReservasi(User $pelanggan, Mobil $mobil): Reservasi
{
    return $pelanggan->reservasis()->create([
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => today()->addDays(2)->toDateString(),
        'tanggal_selesai' => today()->addDays(3)->toDateString(),
        'durasi_sewa' => 2,
        'tarif_harian' => 350000,
        'total_biaya' => 700000,
        'status' => 'disetujui',
    ]);
}

test('an admin can view a paginated customer list with reservation counts', function () {
    $admin = adminPelangganUser('admin');

    foreach (range(1, 11) as $number) {
        adminPelangganUser('pelanggan', [
            'name' => 'Pelanggan Daftar '.$number,
            'email' => "pelanggan-daftar-{$number}@example.test",
        ]);
    }
    adminPelangganUser('admin', [
        'name' => 'Admin Tidak Ditampilkan',
        'email' => 'admin-tersembunyi@example.test',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.pelanggan.index'))
        ->assertOk()
        ->assertSee('Daftar Pelanggan')
        ->assertSee('Pelanggan Daftar 1')
        ->assertDontSee('Admin Tidak Ditampilkan')
        ->assertViewHas('pelanggans', fn ($pelanggans) => $pelanggans->total() === 11 && $pelanggans->perPage() === 10);
});

test('an admin can search customers by name or email', function () {
    $admin = adminPelangganUser('admin');
    $matchedByName = adminPelangganUser('pelanggan', [
        'name' => 'Siti Pencarian',
        'email' => 'siti@example.test',
    ]);
    $matchedByEmail = adminPelangganUser('pelanggan', [
        'name' => 'Pelanggan Lain',
        'email' => 'cari-email@example.test',
    ]);
    adminPelangganUser('pelanggan', [
        'name' => 'Tidak Cocok',
        'email' => 'tidak-cocok@example.test',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.pelanggan.index', ['search' => 'Siti']))
        ->assertOk()
        ->assertSee($matchedByName->email)
        ->assertDontSee($matchedByEmail->email);

    $this->actingAs($admin)
        ->get(route('admin.pelanggan.index', ['search' => 'cari-email']))
        ->assertOk()
        ->assertSee($matchedByEmail->name)
        ->assertDontSee($matchedByName->email);
});

test('an admin can view a customer detail and reservation history', function () {
    $admin = adminPelangganUser('admin');
    $pelanggan = adminPelangganUser('pelanggan', [
        'name' => 'Andi Pelanggan',
        'email' => 'andi@example.test',
    ]);
    $mobil = adminPelangganMobil();
    $reservasi = adminPelangganReservasi($pelanggan, $mobil);

    $this->actingAs($admin)
        ->get(route('admin.pelanggan.show', $pelanggan))
        ->assertOk()
        ->assertSee('Informasi Pelanggan')
        ->assertSee($pelanggan->name)
        ->assertSee($pelanggan->email)
        ->assertSee('1 reservasi')
        ->assertSee('Riwayat Reservasi')
        ->assertSee($mobil->nama_mobil)
        ->assertSee($mobil->merek)
        ->assertSee($mobil->plat_nomor)
        ->assertSee('700.000')
        ->assertSee('Disetujui')
        ->assertSee($reservasi->tanggal_mulai->format('d M Y'));
});

test('only admins can view customer data and admin accounts are not customer details', function () {
    $pelanggan = adminPelangganUser('pelanggan');
    $admin = adminPelangganUser('admin');

    $this->get(route('admin.pelanggan.index'))
        ->assertRedirect(route('login'));

    $this->actingAs($pelanggan)
        ->get(route('admin.pelanggan.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('admin.pelanggan.show', $admin))
        ->assertNotFound();
});
