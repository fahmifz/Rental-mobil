<?php

namespace Database\Seeders;

use App\Models\Mobil;
use App\Models\Rating;
use App\Models\Reservasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CollaborativeFilteringDummySeeder extends Seeder
{
    public const DEMO_PASSWORD = 'cf-demo-2026';

    /** @var array<int, array{nama_mobil: string, merek: string, harga: int}> */
    private const MOBILS = [
        ['nama_mobil' => 'CF Dummy Avanza', 'merek' => 'Toyota', 'harga' => 350000],
        ['nama_mobil' => 'CF Dummy Xenia', 'merek' => 'Daihatsu', 'harga' => 325000],
        ['nama_mobil' => 'CF Dummy Innova', 'merek' => 'Toyota', 'harga' => 500000],
        ['nama_mobil' => 'CF Dummy Ertiga', 'merek' => 'Suzuki', 'harga' => 340000],
        ['nama_mobil' => 'CF Dummy Brio', 'merek' => 'Honda', 'harga' => 275000],
        ['nama_mobil' => 'CF Dummy Rush', 'merek' => 'Toyota', 'harga' => 400000],
        ['nama_mobil' => 'CF Dummy Terios', 'merek' => 'Daihatsu', 'harga' => 390000],
        ['nama_mobil' => 'CF Dummy Calya', 'merek' => 'Toyota', 'harga' => 300000],
        ['nama_mobil' => 'CF Dummy Sigra', 'merek' => 'Daihatsu', 'harga' => 295000],
        ['nama_mobil' => 'CF Dummy Pajero', 'merek' => 'Mitsubishi', 'harga' => 650000],
    ];

    /**
     * Every customer rates five of ten cars. Cars 1 and 2 are shared by all
     * customers, which guarantees the minimum two co-rated items for UBCF.
     *
     * @var array<int, array<int, int>>
     */
    private const RATING_PATTERNS = [
        1 => [1 => 5, 2 => 4, 3 => 5, 4 => 4, 5 => 3],
        2 => [1 => 5, 2 => 4, 3 => 4, 6 => 5, 7 => 4],
        3 => [1 => 4, 2 => 5, 4 => 4, 6 => 5, 8 => 4],
        4 => [1 => 5, 2 => 3, 5 => 4, 7 => 5, 9 => 4],
        5 => [1 => 4, 2 => 4, 3 => 5, 8 => 5, 10 => 4],
        6 => [1 => 3, 2 => 5, 4 => 5, 6 => 4, 9 => 5],
        7 => [1 => 5, 2 => 5, 3 => 4, 7 => 4, 10 => 5],
        8 => [1 => 4, 2 => 3, 5 => 5, 6 => 4, 8 => 5],
        9 => [1 => 3, 2 => 4, 4 => 3, 7 => 5, 9 => 4],
        10 => [1 => 5, 2 => 4, 3 => 5, 6 => 5, 10 => 4],
        11 => [1 => 4, 2 => 5, 5 => 4, 8 => 4, 9 => 5],
        12 => [1 => 5, 2 => 3, 4 => 4, 7 => 5, 10 => 3],
        13 => [1 => 3, 2 => 5, 3 => 4, 6 => 5, 8 => 4],
        14 => [1 => 4, 2 => 4, 5 => 3, 7 => 4, 9 => 5],
        15 => [1 => 5, 2 => 5, 4 => 5, 8 => 4, 10 => 5],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $password = Hash::make(self::DEMO_PASSWORD);
            $mobils = $this->seedMobils();

            foreach (self::RATING_PATTERNS as $customerNumber => $ratings) {
                $customer = User::query()->firstOrCreate(
                    ['email' => $this->customerEmail($customerNumber)],
                    [
                        'name' => sprintf('CF Dummy Pelanggan %02d', $customerNumber),
                        'password' => $password,
                    ],
                );

                foreach ($ratings as $mobilNumber => $ratingValue) {
                    $mobil = $mobils[$mobilNumber];
                    $startDate = Carbon::create(2025, 1, 1)
                        ->addDays(($customerNumber - 1) * 20 + $mobilNumber);
                    $endDate = $startDate->copy()->addDays(2);

                    $reservasi = Reservasi::query()
                        ->where('user_id', $customer->id)
                        ->where('mobil_id', $mobil->id)
                        ->whereDate('tanggal_mulai', $startDate->toDateString())
                        ->whereDate('tanggal_selesai', $endDate->toDateString())
                        ->first();

                    if ($reservasi === null) {
                        $reservasi = $customer->reservasis()->create([
                            'mobil_id' => $mobil->id,
                            'tanggal_mulai' => $startDate->toDateString(),
                            'tanggal_selesai' => $endDate->toDateString(),
                            'durasi_sewa' => 3,
                            'tarif_harian' => $mobil->harga_sewa_per_hari,
                            'total_biaya' => (float) $mobil->harga_sewa_per_hari * 3,
                            'status' => 'selesai',
                        ]);
                    }

                    Rating::query()->firstOrCreate(
                        ['reservasi_id' => $reservasi->id],
                        [
                            'user_id' => $customer->id,
                            'mobil_id' => $mobil->id,
                            'rating' => $ratingValue,
                        ],
                    );
                }
            }
        });
    }

    /** @return array<int, Mobil> */
    private function seedMobils(): array
    {
        $mobils = [];

        foreach (self::MOBILS as $number => $data) {
            $number++;
            $mobils[$number] = Mobil::query()->firstOrCreate(
                ['plat_nomor' => sprintf('CFD-%02d-RCM', $number)],
                [
                    'nama_mobil' => $data['nama_mobil'],
                    'merek' => $data['merek'],
                    'tipe' => 'Dataset UBCF',
                    'tahun' => 2025,
                    'transmisi' => 'Automatic',
                    'kapasitas_penumpang' => 5,
                    'harga_sewa_per_hari' => $data['harga'],
                    'deskripsi' => 'DATA DUMMY UBCF - hanya untuk pengujian rekomendasi.',
                    'status' => 'tersedia',
                ],
            );
        }

        return $mobils;
    }

    private function customerEmail(int $customerNumber): string
    {
        return sprintf('cf-dummy-pelanggan-%02d@example.test', $customerNumber);
    }
}
