<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelReservasiRequest;
use App\Models\Mobil;
use App\Models\Refund;
use App\Models\Reservasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Illuminate\View\View;

class AdminReservasiController extends Controller
{
    public function index(): View
    {
        $reservasis = Reservasi::query()
            ->with(['user', 'mobil', 'pembayarans'])
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

    public function cancel(CancelReservasiRequest $request, Reservasi $reservasi): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($request, $reservasi, $data): void {
                $mobil = Mobil::query()
                    ->lockForUpdate()
                    ->findOrFail($reservasi->mobil_id);

                $lockedReservasi = Reservasi::query()
                    ->with('refund')
                    ->lockForUpdate()
                    ->findOrFail($reservasi->id);

                if (! in_array($lockedReservasi->status, ['menunggu_konfirmasi', 'disetujui'], true)) {
                    throw ValidationException::withMessages([
                        'reservasi' => 'Reservasi dengan status ' . ucwords(str_replace('_', ' ', $lockedReservasi->status)) . ' tidak dapat dibatalkan.',
                    ]);
                }

                if ($lockedReservasi->refund !== null) {
                    throw ValidationException::withMessages([
                        'refund' => 'Keputusan refund untuk reservasi ini sudah tercatat.',
                    ]);
                }

                $totalPembayaran = $lockedReservasi->totalPaid();
                $refundData = $totalPembayaran > 0
                    ? $this->refundDecisionData($data)
                    : null;
                $alasanPembatalan = trim((string) ($data['alasan_pembatalan'] ?? ''))
                    ?: ($refundData['alasan'] ?? null);

                $lockedReservasi->update([
                    'status' => 'dibatalkan',
                    'dibatalkan_pada' => now(),
                    'dibatalkan_oleh_id' => $request->user()->id,
                    'alasan_pembatalan' => $alasanPembatalan,
                ]);

                if ($refundData !== null) {
                    Refund::query()->create([
                        'reservasi_id' => $lockedReservasi->id,
                        'jenis_refund' => $refundData['jenis_refund'],
                        'nominal_refund' => $refundData['nominal_refund'],
                        'status' => $refundData['status'],
                        'alasan' => $refundData['alasan'],
                        'diputuskan_pada' => now(),
                        'diputuskan_oleh_id' => $request->user()->id,
                    ]);
                }

                $mobil->syncOperationalStatus(today());
            });
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'refund' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('admin.reservasi.index')
            ->with('success', 'Reservasi berhasil dibatalkan.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{jenis_refund: string, nominal_refund: float, status: string, alasan: string}
     */
    private function refundDecisionData(array $data): array
    {
        $errors = [];

        foreach (['jenis_refund', 'nominal_refund', 'alasan_refund'] as $field) {
            if (! array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                $errors[$field] = 'Keputusan refund wajib diisi untuk reservasi yang sudah memiliki pembayaran.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $jenisRefund = (string) $data['jenis_refund'];
        $alasanRefund = trim((string) $data['alasan_refund']);

        if ($alasanRefund === '') {
            throw ValidationException::withMessages([
                'alasan_refund' => 'Alasan keputusan refund wajib diisi.',
            ]);
        }

        return [
            'jenis_refund' => $jenisRefund,
            'nominal_refund' => round((float) $data['nominal_refund'], 2),
            'status' => $jenisRefund === 'tidak_ada'
                ? 'tidak_ada_refund'
                : 'menunggu_pengembalian',
            'alasan' => $alasanRefund,
        ];
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
                if ($lockedReservasi->tanggal_selesai->isBefore(today())) {
                    throw ValidationException::withMessages([
                        'reservasi' => 'Reservasi yang tanggal selesai sewanya sudah lewat tidak dapat disetujui.',
                    ]);
                }

                if ($mobil->status === 'perawatan') {
                    throw ValidationException::withMessages([
                        'reservasi' => 'Mobil yang sedang dalam perawatan tidak dapat disetujui untuk disewa.',
                    ]);
                }

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

                $lockedReservasi->update(['status' => $status]);

                if ($lockedReservasi->isActiveOn(today())) {
                    $mobil->syncOperationalStatus(today());
                }

                return;
            }

            $lockedReservasi->update(['status' => $status]);
        });

        $statusLabel = $status === 'disetujui' ? 'disetujui' : 'ditolak';

        return redirect()
            ->route('admin.reservasi.index')
            ->with('success', "Reservasi berhasil {$statusLabel}.");
    }
}
