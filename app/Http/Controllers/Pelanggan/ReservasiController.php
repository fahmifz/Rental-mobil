<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservasiRequest;
use App\Http\Requests\StoreRatingRequest;
use App\Models\Mobil;
use App\Models\Rating;
use App\Models\Reservasi;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReservasiController extends Controller
{
    public function index(Request $request): View
    {
        $reservasis = $request->user()
            ->reservasis()
            ->with(['mobil', 'rating'])
            ->latest()
            ->paginate(10);

        $pendingRatingsCount = $request->user()
            ->reservasis()
            ->where('status', 'selesai')
            ->whereDoesntHave('rating')
            ->count();

        return view('pages.reservasi.index', [
            'reservasis' => $reservasis,
            'pendingRatingsCount' => $pendingRatingsCount,
            'title' => 'Reservasi Saya',
        ]);
    }

    public function create(): View
    {
        $mobils = Mobil::query()
            ->where('status', 'tersedia')
            ->orderBy('nama_mobil')
            ->get();

        return view('pages.reservasi.create', [
            'mobils' => $mobils,
            'title' => 'Buat Reservasi',
        ]);
    }

    public function store(StoreReservasiRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $reservasi = DB::transaction(function () use ($data, $request): Reservasi {
            $mobil = Mobil::query()
                ->lockForUpdate()
                ->findOrFail($data['mobil_id']);

            if ($mobil->status !== 'tersedia') {
                throw ValidationException::withMessages([
                    'mobil_id' => 'Mobil ini tidak tersedia untuk reservasi.',
                ]);
            }

            $hasConflict = $mobil->reservasis()
                ->whereIn('status', ['menunggu_konfirmasi', 'disetujui'])
                ->whereDate('tanggal_mulai', '<=', $data['tanggal_selesai'])
                ->whereDate('tanggal_selesai', '>=', $data['tanggal_mulai'])
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages([
                    'tanggal_mulai' => 'Jadwal mobil bertabrakan dengan reservasi lain. Silakan pilih tanggal berbeda.',
                ]);
            }

            $durasiSewa = (int) Carbon::parse($data['tanggal_mulai'])
                ->diffInDays(Carbon::parse($data['tanggal_selesai'])) + 1;
            $tarifHarian = (float) $mobil->harga_sewa_per_hari;

            return $request->user()->reservasis()->create([
                'mobil_id' => $mobil->id,
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'durasi_sewa' => $durasiSewa,
                'tarif_harian' => $tarifHarian,
                'total_biaya' => round($tarifHarian * $durasiSewa, 2),
                'status' => 'menunggu_konfirmasi',
            ]);
        });

        return redirect()
            ->route('reservasi.show', $reservasi)
            ->with('success', 'Reservasi berhasil dikirim dan menunggu konfirmasi.');
    }

    public function storeRating(StoreRatingRequest $request, int $reservasi): RedirectResponse
    {
        DB::transaction(function () use ($request, $reservasi): void {
            $lockedReservasi = $request->user()
                ->reservasis()
                ->lockForUpdate()
                ->with('rating')
                ->findOrFail($reservasi);

            if ($lockedReservasi->status !== 'selesai') {
                throw ValidationException::withMessages([
                    'rating' => 'Rating hanya dapat diberikan setelah reservasi selesai.',
                ]);
            }

            if ($lockedReservasi->rating !== null) {
                throw ValidationException::withMessages([
                    'rating' => 'Reservasi ini sudah diberi rating.',
                ]);
            }

            Rating::query()->create([
                'reservasi_id' => $lockedReservasi->id,
                'user_id' => $request->user()->id,
                'mobil_id' => $lockedReservasi->mobil_id,
                'rating' => $request->validated()['rating'],
            ]);
        });

        return redirect()
            ->route('reservasi.show', $reservasi)
            ->with('success', 'Rating mobil berhasil disimpan.');
    }

    public function show(Request $request, int $reservasi): View
    {
        $reservasi = $request->user()
            ->reservasis()
            ->with(['mobil', 'rating'])
            ->findOrFail($reservasi);

        return view('pages.reservasi.show', [
            'reservasi' => $reservasi,
            'title' => 'Detail Reservasi',
        ]);
    }
}
