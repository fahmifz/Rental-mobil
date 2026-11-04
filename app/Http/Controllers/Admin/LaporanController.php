<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Refund;
use App\Models\Reservasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'tanggal_awal' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:tanggal_akhir'],
            'tanggal_akhir' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:tanggal_awal'],
        ]);

        $tanggalAwal = $data['tanggal_awal'] ?? null;
        $tanggalAkhir = $data['tanggal_akhir'] ?? null;

        $reservasiQuery = $this->filterPeriod(Reservasi::query(), 'created_at', $tanggalAwal, $tanggalAkhir);
        $totalReservasi = (clone $reservasiQuery)->count();
        $totalReservasiSelesai = (clone $reservasiQuery)->where('status', 'selesai')->count();
        $totalReservasiDibatalkan = (clone $reservasiQuery)->where('status', 'dibatalkan')->count();

        $totalPembayaranMasuk = (float) $this->filterPeriod(
            Pembayaran::query(),
            'tanggal_pembayaran',
            $tanggalAwal,
            $tanggalAkhir,
        )->sum('nominal_pembayaran');

        $totalRefundDikembalikan = (float) $this->filterPeriod(
            Refund::query()->where('status', 'dikembalikan'),
            'tanggal_refund',
            $tanggalAwal,
            $tanggalAkhir,
        )->sum('nominal_refund');

        $reservasis = (clone $reservasiQuery)
            ->with(['user', 'mobil'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.laporan.index', [
            'reservasis' => $reservasis,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'totalReservasi' => $totalReservasi,
            'totalReservasiSelesai' => $totalReservasiSelesai,
            'totalReservasiDibatalkan' => $totalReservasiDibatalkan,
            'totalPembayaranMasuk' => round($totalPembayaranMasuk, 2),
            'totalRefundDikembalikan' => round($totalRefundDikembalikan, 2),
            'pendapatanBersih' => round($totalPembayaranMasuk - $totalRefundDikembalikan, 2),
            'title' => 'Laporan',
        ]);
    }

    private function filterPeriod(
        Builder $query,
        string $column,
        ?string $tanggalAwal,
        ?string $tanggalAkhir,
    ): Builder {
        if ($tanggalAwal !== null) {
            $query->whereDate($column, '>=', $tanggalAwal);
        }

        if ($tanggalAkhir !== null) {
            $query->whereDate($column, '<=', $tanggalAkhir);
        }

        return $query;
    }
}
