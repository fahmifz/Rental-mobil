<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mobil;
use App\Models\Reservasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminReservasiController extends Controller
{
    public function index(): View
    {
        $reservasis = Reservasi::query()
            ->with(['user', 'mobil'])
            ->latest()
            ->paginate(10);

        return view('pages.admin.reservasi.index', [
            'reservasis' => $reservasis,
            'title' => 'Reservasi / Penyewaan',
        ]);
    }

    public function approve(Reservasi $reservasi): RedirectResponse
    {
        return $this->updatePendingReservation($reservasi, 'disetujui');
    }

    public function reject(Reservasi $reservasi): RedirectResponse
    {
        return $this->updatePendingReservation($reservasi, 'ditolak');
    }

    public function complete(Reservasi $reservasi): RedirectResponse
    {
        DB::transaction(function () use ($reservasi): void {
            $lockedReservasi = Reservasi::query()
                ->lockForUpdate()
                ->findOrFail($reservasi->id);

            if ($lockedReservasi->status !== 'disetujui') {
                throw ValidationException::withMessages([
                    'reservasi' => 'Hanya reservasi yang disetujui yang dapat diselesaikan.',
                ]);
            }

            if ($lockedReservasi->tanggal_selesai->isAfter(today())) {
                throw ValidationException::withMessages([
                    'reservasi' => 'Reservasi belum dapat diselesaikan sebelum tanggal selesai sewa.',
                ]);
            }

            $lockedReservasi->update(['status' => 'selesai']);
        });

        return redirect()
            ->route('admin.reservasi.index')
            ->with('success', 'Reservasi berhasil diselesaikan.');
    }

    private function updatePendingReservation(Reservasi $reservasi, string $status): RedirectResponse
    {
        DB::transaction(function () use ($reservasi, $status): void {
            $mobil = Mobil::query()
                ->lockForUpdate()
                ->findOrFail($reservasi->mobil_id);

            $lockedReservasi = Reservasi::query()
                ->lockForUpdate()
                ->findOrFail($reservasi->id);

            if ($lockedReservasi->status !== 'menunggu_konfirmasi') {
                throw ValidationException::withMessages([
                    'reservasi' => 'Reservasi ini sudah diproses dan tidak dapat diubah lagi.',
                ]);
            }

            if ($status === 'disetujui') {
                $hasConflict = $mobil->reservasis()
                    ->where('id', '!=', $lockedReservasi->id)
                    ->whereIn('status', ['menunggu_konfirmasi', 'disetujui'])
                    ->whereDate('tanggal_mulai', '<=', $lockedReservasi->tanggal_selesai->toDateString())
                    ->whereDate('tanggal_selesai', '>=', $lockedReservasi->tanggal_mulai->toDateString())
                    ->exists();

                if ($hasConflict) {
                    throw ValidationException::withMessages([
                        'reservasi' => 'Jadwal mobil bertabrakan dengan reservasi lain yang masih aktif.',
                    ]);
                }
            }

            $lockedReservasi->update(['status' => $status]);
        });

        $statusLabel = $status === 'disetujui' ? 'disetujui' : 'ditolak';

        return redirect()
            ->route('admin.reservasi.index')
            ->with('success', "Reservasi berhasil {$statusLabel}.");
    }
}
