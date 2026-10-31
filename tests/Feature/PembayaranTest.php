<?php

use App\Models\Mobil;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function pembayaranUser(string $role): User
{
    $user = User::factory()->create();
    $user->role = $role;
    $user->save();

    return $user;
}

function pembayaranMobil(string $plate = 'DD 8001 PAY', string $status = 'disewa'): Mobil
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

function pembayaranReservasi(
    User $customer,
    Mobil $mobil,
    string $status = 'disetujui',
    float $total = 700000,
): Reservasi {
    return $customer->reservasis()->create([
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => today()->subDays(2)->toDateString(),
        'tanggal_selesai' => today()->toDateString(),
        'durasi_sewa' => 3,
        'tarif_harian' => $total / 2,
        'total_biaya' => $total,
        'status' => $status,
    ]);
}

function pembayaranPayload(float $nominal, string $metode = 'tunai'): array
{
    return [
        'nominal_pembayaran' => $nominal,
        'tanggal_pembayaran' => today()->toDateString(),
        'metode_pembayaran' => $metode,
        'catatan' => 'Dicatat melalui loket rental',
    ];
}

test('an admin can view the manual payment list', function () {
    $admin = pembayaranUser('admin');
    $customer = pembayaranUser('pelanggan');
    $mobil = pembayaranMobil();
    pembayaranReservasi($customer, $mobil);

    $this->actingAs($admin)
        ->get(route('admin.pembayaran.index'))
        ->assertOk()
        ->assertSee('Daftar Pembayaran')
        ->assertSee($customer->name)
        ->assertSee($mobil->nama_mobil)
        ->assertSee('700.000');
});

test('customers cannot access admin payment endpoints', function () {
    $customer = pembayaranUser('pelanggan');
    $mobil = pembayaranMobil('DD 8002 PAY');
    $reservasi = pembayaranReservasi($customer, $mobil);

    $this->actingAs($customer)
        ->get(route('admin.pembayaran.index'))
        ->assertForbidden();

    $this->actingAs($customer)
        ->get(route('admin.pembayaran.create', $reservasi))
        ->assertForbidden();

    $this->actingAs($customer)
        ->post(route('admin.pembayaran.store', $reservasi), pembayaranPayload(350000))
        ->assertForbidden();
});

test('cash and transfer payments can be recorded with correct partial and paid statuses', function () {
    $admin = pembayaranUser('admin');
    $customer = pembayaranUser('pelanggan');

    $partialMobil = pembayaranMobil('DD 8003 PAY');
    $partial = pembayaranReservasi($customer, $partialMobil);
    $this->actingAs($admin)
        ->post(route('admin.pembayaran.store', $partial), pembayaranPayload(350000, 'tunai'))
        ->assertRedirect(route('admin.pembayaran.show', $partial))
        ->assertSessionHas('success');

    $partialPayment = Pembayaran::query()->where('reservasi_id', $partial->id)->firstOrFail();
    expect((float) $partialPayment->nominal_pembayaran)->toBe(350000.0)
        ->and($partialPayment->status)->toBe('dibayar_sebagian')
        ->and($partialPayment->metode_pembayaran)->toBe('tunai');

    $paidMobil = pembayaranMobil('DD 8004 PAY');
    $paid = pembayaranReservasi($customer, $paidMobil);
    $this->actingAs($admin)
        ->post(route('admin.pembayaran.store', $paid), pembayaranPayload(700000, 'transfer'))
        ->assertRedirect(route('admin.pembayaran.show', $paid));

    $paidPayment = Pembayaran::query()->where('reservasi_id', $paid->id)->firstOrFail();
    expect((float) $paidPayment->nominal_pembayaran)->toBe(700000.0)
        ->and($paidPayment->status)->toBe('lunas')
        ->and($paidPayment->metode_pembayaran)->toBe('transfer');
});

test('zero negative and excessive payment amounts are rejected', function () {
    $admin = pembayaranUser('admin');
    $customer = pembayaranUser('pelanggan');
    $mobil = pembayaranMobil('DD 8005 PAY');
    $reservasi = pembayaranReservasi($customer, $mobil);

    foreach ([0, -100] as $nominal) {
        $this->actingAs($admin)
            ->from(route('admin.pembayaran.create', $reservasi))
            ->post(route('admin.pembayaran.store', $reservasi), pembayaranPayload($nominal))
            ->assertRedirect(route('admin.pembayaran.create', $reservasi))
            ->assertSessionHasErrors('nominal_pembayaran');
    }

    $this->actingAs($admin)
        ->from(route('admin.pembayaran.create', $reservasi))
        ->post(route('admin.pembayaran.store', $reservasi), pembayaranPayload(700000.01))
        ->assertRedirect(route('admin.pembayaran.create', $reservasi))
        ->assertSessionHasErrors('nominal_pembayaran');

    expect(Pembayaran::query()->count())->toBe(0);
});

test('pending rejected and cancelled reservations cannot be paid', function () {
    $admin = pembayaranUser('admin');
    $customer = pembayaranUser('pelanggan');

    foreach (['menunggu_konfirmasi', 'ditolak', 'dibatalkan'] as $index => $status) {
        $mobil = pembayaranMobil("DD 800{$index}6 PAY", 'tersedia');
        $reservasi = pembayaranReservasi($customer, $mobil, $status);

        $this->actingAs($admin)
            ->post(route('admin.pembayaran.store', $reservasi), pembayaranPayload(350000))
            ->assertSessionHasErrors('reservasi');
    }

    expect(Pembayaran::query()->count())->toBe(0);
});

test('a completed reservation with outstanding balance can be paid and a fully paid completed reservation is recorded as paid', function () {
    $admin = pembayaranUser('admin');
    $customer = pembayaranUser('pelanggan');
    $partialMobil = pembayaranMobil('DD 8007 PAY', 'tersedia');
    $partial = pembayaranReservasi($customer, $partialMobil, 'selesai');

    $this->actingAs($admin)
        ->post(route('admin.pembayaran.store', $partial), pembayaranPayload(200000))
        ->assertSessionHasNoErrors();

    expect(Pembayaran::query()->where('reservasi_id', $partial->id)->firstOrFail()->status)
        ->toBe('dibayar_sebagian');

    $paidMobil = pembayaranMobil('DD 8008 PAY', 'tersedia');
    $paid = pembayaranReservasi($customer, $paidMobil, 'selesai');
    $this->actingAs($admin)
        ->post(route('admin.pembayaran.store', $paid), pembayaranPayload(700000))
        ->assertSessionHasNoErrors();

    $payment = Pembayaran::query()->where('reservasi_id', $paid->id)->firstOrFail();
    expect($payment->status)->toBe('lunas');
});

test('two transaction payments produce partial then paid status with separate history records', function () {
    $admin = pembayaranUser('admin');
    $customer = pembayaranUser('pelanggan');
    $mobil = pembayaranMobil('DD 8013 PAY');
    $reservasi = pembayaranReservasi($customer, $mobil, 'disetujui', 2400000);

    $this->actingAs($admin)
        ->post(route('admin.pembayaran.store', $reservasi), pembayaranPayload(2000000));

    expect($reservasi->totalPaid())->toBe(2000000.0)
        ->and($reservasi->remainingAmount())->toBe(400000.0)
        ->and($reservasi->paymentStatus())->toBe('dibayar_sebagian');

    $this->actingAs($admin)
        ->post(route('admin.pembayaran.store', $reservasi), pembayaranPayload(400000, 'transfer'));

    $payments = Pembayaran::query()->where('reservasi_id', $reservasi->id)->orderBy('id')->get();
    expect($payments)->toHaveCount(2)
        ->and($reservasi->totalPaid())->toBe(2400000.0)
        ->and($reservasi->remainingAmount())->toBe(0.0)
        ->and($reservasi->paymentStatus())->toBe('lunas');
});

test('repeated form submission with the same idempotency key creates only one transaction', function () {
    $admin = pembayaranUser('admin');
    $customer = pembayaranUser('pelanggan');
    $mobil = pembayaranMobil('DD 8009 PAY');
    $reservasi = pembayaranReservasi($customer, $mobil);

    $payload = pembayaranPayload(350000);
    $payload['idempotency_key'] = 'repeat-payment-key';

    $this->actingAs($admin)
        ->post(route('admin.pembayaran.store', $reservasi), $payload);
    $this->actingAs($admin)
        ->post(route('admin.pembayaran.store', $reservasi), $payload);

    expect(Pembayaran::query()->where('reservasi_id', $reservasi->id)->count())->toBe(1)
        ->and((float) Pembayaran::query()->firstOrFail()->nominal_pembayaran)->toBe(350000.0);
});

test('separate transactions calculate total, remaining balance, status, and last admin', function () {
    $admin1 = pembayaranUser('admin');
    $admin2 = pembayaranUser('admin');
    $customer = pembayaranUser('pelanggan');
    $mobil = pembayaranMobil('DD 8010 PAY');
    $reservasi = pembayaranReservasi($customer, $mobil, 'disetujui', 700000);

    $this->actingAs($admin1)
        ->post(route('admin.pembayaran.store', $reservasi), pembayaranPayload(200000));
    $firstPayment = Pembayaran::query()->where('reservasi_id', $reservasi->id)->firstOrFail();

    $this->actingAs($admin2)
        ->post(route('admin.pembayaran.store', $reservasi), pembayaranPayload(500000, 'transfer'))
        ->assertRedirect(route('admin.pembayaran.show', $reservasi))
        ->assertSessionHas('success');

    $payments = Pembayaran::query()->where('reservasi_id', $reservasi->id)->orderBy('id')->get();
    expect($payments)->toHaveCount(2)
        ->and((float) $firstPayment->nominal_pembayaran)->toBe(200000.0)
        ->and((float) $payments->sum('nominal_pembayaran'))->toBe(700000.0)
        ->and($reservasi->totalPaid())->toBe(700000.0)
        ->and($reservasi->remainingAmount())->toBe(0.0)
        ->and($reservasi->paymentStatus())->toBe('lunas')
        ->and($payments->last()->dicatat_oleh_id)->toBe($admin2->id);
});

test('recording payment does not change reservation or car operational status', function () {
    $admin = pembayaranUser('admin');
    $customer = pembayaranUser('pelanggan');
    $mobil = pembayaranMobil('DD 8011 PAY', 'disewa');
    $reservasi = pembayaranReservasi($customer, $mobil, 'disetujui');
    $originalReservationStatus = $reservasi->status;
    $originalCarStatus = $mobil->status;

    $this->actingAs($admin)
        ->post(route('admin.pembayaran.store', $reservasi), pembayaranPayload(700000));

    expect($reservasi->fresh()->status)->toBe($originalReservationStatus)
        ->and($mobil->fresh()->status)->toBe($originalCarStatus);
});

test('simulated concurrent payment attempts cannot exceed the reservation total', function () {
    $admin = pembayaranUser('admin');
    $customer = pembayaranUser('pelanggan');
    $mobil = pembayaranMobil('DD 8012 PAY');
    $reservasi = pembayaranReservasi($customer, $mobil);

    foreach ([700000, 700000.01] as $nominal) {
        $this->actingAs($admin)
            ->post(route('admin.pembayaran.store', $reservasi), pembayaranPayload($nominal));
    }

    $payments = Pembayaran::query()->where('reservasi_id', $reservasi->id)->get();
    expect($payments)->toHaveCount(1)
        ->and((float) $payments->first()->nominal_pembayaran)->toBeLessThanOrEqual((float) $reservasi->total_biaya);
});
