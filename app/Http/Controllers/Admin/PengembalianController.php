<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengembalianRequest;
use App\Models\Mobil;
use App\Models\Pengembalian;
use App\Models\Reservasi;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PengembalianController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $tab = $request->query('tab', 'siap_kembali');

        $query = Reservasi::query()
            ->with(['user', 'mobil', 'pembayarans', 'pengembalian.diprosesOleh']);

        if ($tab === 'riwayat') {
            $query->whereHas('pengembalian');
        } else {
            $query->where('status', 'disetujui')
                ->whereDoesntHave('pengembalian');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->whereHas('user', function ($sub) use ($search): void {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('mobil', function ($sub) use ($search): void {
                    $sub->where('nama_mobil', 'like', "%{$search}%")
                        ->orWhere('plat_nomor', 'like', "%{$search}%");
                });
            });
        }

        $reservasis = $query->latest()->paginate(10)->withQueryString();

        $siapKembaliCount = Reservasi::query()
            ->where('status', 'disetujui')
            ->whereDoesntHave('pengembalian')
            ->count();

        $riwayatCount = Reservasi::query()
            ->whereHas('pengembalian')
            ->count();

        return view('pages.admin.pengembalian.index', [
            'reservasis' => $reservasis,
            'search' => $search,
            'tab' => $tab,
            'siapKembaliCount' => $siapKembaliCount,
            'riwayatCount' => $riwayatCount,
            'title' => 'Pengembalian Mobil',
        ]);
    }

    public function create(Reservasi $reservasi): View
    {
        $reservasi->load(['user', 'mobil', 'pembayarans']);

        if ($reservasi->status !== 'disetujui' || $reservasi->pengembalian()->exists()) {
            throw ValidationException::withMessages([
                'reservasi' => 'Hanya reservasi yang telah disetujui dan belum dikembalikan yang dapat diproses.',
            ]);
        }

        $now = now();
        $defaultReturnTime = $now->format('Y-m-d\TH:i');
        $initialCalc = Pengembalian::calculateLateReturn($reservasi, $now);

        return view('pages.admin.pengembalian.create', [
            'reservasi' => $reservasi,
            'defaultReturnTime' => $defaultReturnTime,
            'initialCalc' => $initialCalc,
            'title' => 'Proses Pengembalian Mobil #' . $reservasi->id,
        ]);
    }

    public function store(StorePengembalianRequest $request, Reservasi $reservasi): RedirectResponse
    {
        DB::transaction(function () use ($request, $reservasi): void {
            $mobil = Mobil::query()
                ->lockForUpdate()
                ->findOrFail($reservasi->mobil_id);

            $lockedReservasi = Reservasi::query()
                ->lockForUpdate()
                ->with('pengembalian')
                ->findOrFail($reservasi->id);

            if ($lockedReservasi->status !== 'disetujui') {
                throw ValidationException::withMessages([
                    'reservasi' => 'Hanya reservasi yang telah disetujui yang dapat diproses pengembaliannya.',
                ]);
            }

            if ($lockedReservasi->pengembalian !== null) {
                throw ValidationException::withMessages([
                    'reservasi' => 'Pengembalian untuk reservasi ini sudah pernah diproses.',
                ]);
            }

            $actualReturn = Carbon::parse($request->validated('tanggal_pengembalian'));

            if ($actualReturn->lt($lockedReservasi->tanggal_mulai)) {
                throw ValidationException::withMessages([
                    'tanggal_pengembalian' => 'Waktu pengembalian aktual tidak boleh sebelum tanggal mulai sewa.',
                ]);
            }

            $lateCalc = Pengembalian::calculateLateReturn($lockedReservasi, $actualReturn);

            $lockedReservasi->pengembalian()->create([
                'tanggal_pengembalian' => $actualReturn->format('Y-m-d H:i:s'),
                'kondisi_mobil' => $request->validated('kondisi_mobil'),
                'catatan_kondisi' => $request->validated('catatan_kondisi'),
                'durasi_keterlambatan' => $lateCalc['durasi_keterlambatan'],
                'tarif_denda_per_hari' => $lateCalc['tarif_denda_per_hari'],
                'total_denda' => $lateCalc['total_denda'],
                'status_mobil_setelah_kembali' => $request->validated('status_mobil_setelah_kembali'),
                'diproses_oleh_id' => $request->user()->id,
            ]);

            $lockedReservasi->update(['status' => 'selesai']);

            $statusMobil = $request->validated('status_mobil_setelah_kembali');
            if ($statusMobil === 'perawatan') {
                $mobil->update(['status' => 'perawatan']);
            } else {
                $mobil->update(['status' => 'tersedia']);
                $mobil->syncOperationalStatus(today());
            }
        });

        return redirect()
            ->route('admin.pengembalian.show', $reservasi)
            ->with('success', 'Pengembalian mobil berhasil dicatat.');
    }

    public function show(Reservasi $reservasi): View|RedirectResponse
    {
        $reservasi->load(['user', 'mobil', 'pembayarans.dicatatOleh', 'pengembalian.diprosesOleh']);

        if ($reservasi->pengembalian === null) {
            return redirect()
                ->route('admin.pengembalian.index')
                ->with('error', 'Reservasi ini belum memiliki catatan pengembalian.');
        }

        return view('pages.admin.pengembalian.show', [
            'reservasi' => $reservasi,
            'pengembalian' => $reservasi->pengembalian,
            'title' => 'Detail Pengembalian Mobil #' . $reservasi->id,
        ]);
    }
}

