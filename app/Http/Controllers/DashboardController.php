<?php

namespace App\Http\Controllers;

use App\Models\Mobil;
use App\Models\Reservasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $mobilStats = Mobil::query()
            ->selectRaw('COUNT(*) as total_mobil')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as mobil_tersedia', ['tersedia'])
            ->first();

        $reservasiStats = Reservasi::query()
            ->selectRaw('COUNT(*) as total_reservasi')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as menunggu_konfirmasi', ['menunggu_konfirmasi'])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as disetujui', ['disetujui'])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as ditolak', ['ditolak'])
            ->first();

        $stats = [
            'totalMobil' => (int) $mobilStats->total_mobil,
            'mobilTersedia' => (int) $mobilStats->mobil_tersedia,
            'totalReservasi' => (int) $reservasiStats->total_reservasi,
            'menungguKonfirmasi' => (int) $reservasiStats->menunggu_konfirmasi,
            'disetujui' => (int) $reservasiStats->disetujui,
            'ditolak' => (int) $reservasiStats->ditolak,
        ];

        $recentReservations = Reservasi::query()
            ->with([
                'user:id,name,email',
                'mobil:id,nama_mobil,merek',
            ])
            ->latest()
            ->take(5)
            ->get();

        $monthlyReservationChart = $this->monthlyReservationChart();

        return view('pages.dashboard.ecommerce', [
            'title' => 'Rental Mobil Dashboard',
            'stats' => $stats,
            'recentReservations' => $recentReservations,
            'monthlyReservationChart' => $monthlyReservationChart,
        ]);
    }

    /**
     * @return array{categories: list<string>, data: list<int>}
     */
    private function monthlyReservationChart(): array
    {
        $firstMonth = now()->startOfMonth()->subMonths(5);
        $lastMonth = now()->endOfMonth();
        $monthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $monthlyCounts = Reservasi::query()
            ->whereBetween('created_at', [$firstMonth, $lastMonth])
            ->selectRaw("{$monthExpression} as month_key, COUNT(*) as total")
            ->groupByRaw($monthExpression)
            ->pluck('total', 'month_key');

        $months = collect(range(0, 5))->map(
            fn (int $offset): Carbon => $firstMonth->copy()->addMonths($offset)
        );

        return [
            'categories' => $months
                ->map(fn (Carbon $month): string => $month->format('M Y'))
                ->all(),
            'data' => $months
                ->map(fn (Carbon $month): int => (int) ($monthlyCounts[$month->format('Y-m')] ?? 0))
                ->all(),
        ];
    }
}
