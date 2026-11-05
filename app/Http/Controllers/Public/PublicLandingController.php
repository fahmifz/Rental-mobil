<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Mobil;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PublicLandingController extends Controller
{
    public function index(): View
    {
        $mobils = Mobil::query()
            ->with('kategori')
            ->where('status', 'tersedia')
            ->latest()
            ->get();

        $heroMobils = $mobils
            ->filter(fn (Mobil $mobil): bool => filled($mobil->foto)
                && Storage::disk('public')->exists($mobil->foto))
            ->take(5)
            ->map(function (Mobil $mobil): Mobil {
                $mobil->setAttribute(
                    'hero_photo_url',
                    Storage::disk('public')->url($mobil->foto)
                );

                return $mobil;
            })
            ->values();

        $availableCategories = $mobils
            ->pluck('kategori')
            ->filter()
            ->unique('id')
            ->sortBy('nama_kategori')
            ->values();

        $availableTransmissions = $mobils
            ->pluck('transmisi')
            ->filter(fn (?string $transmisi): bool => filled($transmisi))
            ->unique()
            ->sort()
            ->values();

        return view('pages.landing', compact(
            'mobils',
            'heroMobils',
            'availableCategories',
            'availableTransmissions'
        ));
    }
}
