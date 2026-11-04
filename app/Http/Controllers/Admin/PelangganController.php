<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelangganController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $pelanggans = User::query()
            ->where('role', 'pelanggan')
            ->withCount('reservasis')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.pelanggan.index', [
            'pelanggans' => $pelanggans,
            'search' => $search,
            'title' => 'Pelanggan',
        ]);
    }

    public function show(User $pelanggan): View
    {
        abort_unless($pelanggan->isPelanggan(), 404);

        $pelanggan->loadCount('reservasis');
        $reservasis = $pelanggan->reservasis()
            ->with('mobil')
            ->latest()
            ->paginate(10);

        return view('pages.admin.pelanggan.show', [
            'pelanggan' => $pelanggan,
            'reservasis' => $reservasis,
            'title' => 'Detail Pelanggan',
        ]);
    }
}
