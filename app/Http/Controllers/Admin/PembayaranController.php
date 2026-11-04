<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePembayaranRequest;
use App\Http\Requests\UpdatePembayaranRequest;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $reservasis = Reservasi::query()
            ->with(['user', 'mobil', 'pembayarans', 'pengembalian'])
            ->whereIn('status', ['disetujui', 'selesai'])
            ->where(function ($query) use ($search): void {
                if ($search !== '') {
                    $query->whereHas('user', function ($query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.pembayaran.index', [
            'reservasis' => $reservasis,
            'search' => $search,
            'title' => 'Pembayaran',
        ]);
    }

    public function show(Reservasi $reservasi): View
    {
        $reservasi->load(['user', 'mobil', 'pembayarans.dicatatOleh', 'pengembalian']);

        return view('pages.admin.pembayaran.show', [
            'reservasi' => $reservasi,
            'title' => 'Detail Pembayaran',
        ]);
    }

    public function create(Reservasi $reservasi): View
    {
        $reservasi->load(['user', 'mobil', 'pembayarans', 'pengembalian']);
        $this->ensureReservasiEligible($reservasi);

        return view('pages.admin.pembayaran.create', [
            'reservasi' => $reservasi,
            'title' => 'Catat Pembayaran',
        ]);
    }

    public function edit(Reservasi $reservasi): View
    {
        return $this->create($reservasi);
    }

    public function store(StorePembayaranRequest $request, Reservasi $reservasi): RedirectResponse
    {
        return $this->recordPayment($reservasi, $request, $request->validated());
    }

    public function update(UpdatePembayaranRequest $request, Pembayaran $pembayaran): RedirectResponse
    {
        return $this->recordPayment($pembayaran->reservasi, $request, $request->validated());
    }

    private function recordPayment(
        Reservasi $reservasi,
        FormRequest $request,
        array $data
    ): RedirectResponse {
        $idempotencyKey = trim((string) ($data['idempotency_key'] ?? ''))
            ?: (string) Str::uuid();

        try {
            DB::transaction(function () use ($data, $reservasi, $request, $idempotencyKey): void {
                $lockedReservasi = Reservasi::query()
                    ->lockForUpdate()
                    ->findOrFail($reservasi->id);

                $this->ensureReservasiEligible($lockedReservasi);

                $existing = Pembayaran::query()
                    ->where('reservasi_id', $lockedReservasi->id)
                    ->where('idempotency_key', $idempotencyKey)
                    ->lockForUpdate()
                    ->first();

                if ($existing !== null) {
                    return;
                }

                $nominal = round((float) $data['nominal_pembayaran'], 2);
                $total = $lockedReservasi->totalAmountDue();
                $totalPaid = round((float) $lockedReservasi->pembayarans()->sum('nominal_pembayaran'), 2);
                $remaining = round($total - $totalPaid, 2);

                if ($nominal <= 0) {
                    throw ValidationException::withMessages([
                        'nominal_pembayaran' => 'Nominal pembayaran harus lebih besar dari nol.',
                    ]);
                }

                if ($nominal > $remaining) {
                    throw ValidationException::withMessages([
                        'nominal_pembayaran' => 'Nominal pembayaran tidak boleh melebihi sisa tagihan sebesar Rp' .
                            number_format($remaining, 0, ',', '.') . '.',
                    ]);
                }

                $newTotalPaid = round($totalPaid + $nominal, 2);
                $status = $newTotalPaid >= $total
                    ? 'lunas'
                    : 'dibayar_sebagian';

                Pembayaran::query()->create([
                    'reservasi_id' => $lockedReservasi->id,
                    'nominal_pembayaran' => $nominal,
                    'tanggal_pembayaran' => $data['tanggal_pembayaran'],
                    'metode_pembayaran' => $data['metode_pembayaran'],
                    'status' => $status,
                    'catatan' => $data['catatan'] ?? null,
                    'dicatat_oleh_id' => $request->user()->id,
                    'idempotency_key' => $idempotencyKey,
                ]);
            });
        } catch (QueryException) {
            return redirect()
                ->route('admin.pembayaran.show', $reservasi)
                ->with('error', 'Pembayaran tidak dapat disimpan karena konflik data. Silakan muat ulang halaman.');
        }

        return redirect()
            ->route('admin.pembayaran.show', $reservasi)
            ->with('success', 'Pembayaran berhasil dicatat.');
    }

    private function ensureReservasiEligible(Reservasi $reservasi): void
    {
        if (! in_array($reservasi->status, ['disetujui', 'selesai'], true)) {
            throw ValidationException::withMessages([
                'reservasi' => 'Pembayaran hanya dapat dicatat untuk reservasi yang disetujui atau selesai.',
            ]);
        }

        $totalPaid = round((float) $reservasi->pembayarans()->sum('nominal_pembayaran'), 2);
        $total = $reservasi->totalAmountDue();

        if ($totalPaid >= $total) {
            throw ValidationException::withMessages([
                'reservasi' => 'Reservasi ini sudah lunas.',
            ]);
        }
    }
}
