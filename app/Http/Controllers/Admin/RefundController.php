<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmRefundRequest;
use App\Models\Refund;
use App\Models\Reservasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class RefundController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $availableStatuses = ['menunggu_pengembalian', 'dikembalikan', 'tidak_ada_refund'];

        $refunds = Refund::query()
            ->with([
                'reservasi.user',
                'reservasi.mobil',
                'reservasi.pembayarans',
                'diputuskanOleh',
                'dikonfirmasiOleh',
            ])
            ->when(in_array($status, $availableStatuses, true), fn ($query) => $query->where('status', $status))
            ->latest('diputuskan_pada')
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.refund.index', [
            'refunds' => $refunds,
            'status' => $status,
            'title' => 'Refund',
        ]);
    }

    public function create(Reservasi $reservasi): View|RedirectResponse
    {
        $reservasi->load(['user', 'mobil', 'pembayarans', 'refund']);

        if (! $reservasi->canBeCancelledByAdmin()) {
            return redirect()
                ->route('admin.reservasi.index')
                ->with('error', 'Reservasi ini tidak dapat dibatalkan.');
        }

        if ($reservasi->refund !== null) {
            return redirect()
                ->route('admin.refund.show', $reservasi->refund)
                ->with('error', 'Keputusan refund untuk reservasi ini sudah tercatat.');
        }

        if ((float) $reservasi->pembayarans->sum('nominal_pembayaran') <= 0) {
            return redirect()
                ->route('admin.reservasi.index')
                ->with('error', 'Reservasi tanpa pembayaran dapat dibatalkan langsung dari daftar reservasi.');
        }

        return view('pages.admin.refund.create', [
            'reservasi' => $reservasi,
            'totalPembayaran' => round((float) $reservasi->pembayarans->sum('nominal_pembayaran'), 2),
            'title' => 'Keputusan Refund',
        ]);
    }

    public function show(Refund $refund): View
    {
        $refund->load([
            'reservasi.user',
            'reservasi.mobil',
            'reservasi.pembayarans',
            'diputuskanOleh',
            'dikonfirmasiOleh',
        ]);

        return view('pages.admin.refund.show', [
            'refund' => $refund,
            'title' => 'Detail Refund',
        ]);
    }

    public function confirm(ConfirmRefundRequest $request, Refund $refund): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($request, $refund, $data): void {
                $lockedRefund = Refund::query()
                    ->lockForUpdate()
                    ->findOrFail($refund->id);

                $lockedReservasi = Reservasi::query()
                    ->lockForUpdate()
                    ->findOrFail($lockedRefund->reservasi_id);

                if ($lockedRefund->status !== 'menunggu_pengembalian') {
                    throw ValidationException::withMessages([
                        'refund' => 'Hanya refund yang menunggu pengembalian dana yang dapat dikonfirmasi.',
                    ]);
                }

                if ($lockedReservasi->status !== 'dibatalkan') {
                    throw ValidationException::withMessages([
                        'refund' => 'Refund hanya dapat dikonfirmasi untuk reservasi yang dibatalkan.',
                    ]);
                }

                $lockedRefund->update([
                    'status' => 'dikembalikan',
                    'metode_refund' => $data['metode_refund'],
                    'tanggal_refund' => $data['tanggal_refund'],
                    'referensi_refund' => $data['referensi_refund'] ?? null,
                    'dikonfirmasi_pada' => now(),
                    'dikonfirmasi_oleh_id' => $request->user()->id,
                ]);
            });
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'refund' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('admin.reservasi.index')
            ->with('success', 'Refund berhasil dikonfirmasi.');
    }
}
