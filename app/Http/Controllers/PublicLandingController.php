<?php

namespace App\Http\Controllers;

use App\Models\Mobil;
use Illuminate\View\View;

class PublicLandingController extends Controller
{
    public function index(): View
    {
        $mobils = Mobil::query()
            ->with('kategori')
            ->where('status', 'tersedia')
            ->latest()
            ->take(6)
            ->get();

        return view('pages.landing', compact('mobils'));
    }
}
