<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKategoriMobilRequest;
use App\Http\Requests\UpdateKategoriMobilRequest;
use App\Models\KategoriMobil;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\QueryException;
use Illuminate\View\View;

class KategoriMobilController extends Controller
{
    public function index(): View
    {
        $kategoriMobils = KategoriMobil::query()
            ->orderBy('nama_kategori')
            ->paginate(10);

        return view('pages.kategori-mobil.index', compact('kategoriMobils'));
    }

    public function create(): View
    {
        return view('pages.kategori-mobil.create');
    }

    public function store(StoreKategoriMobilRequest $request): RedirectResponse
    {
        KategoriMobil::create($request->validated());

        return redirect()
            ->route('kategori-mobil.index')
            ->with('success', 'Car category created successfully.');
    }

    public function edit(KategoriMobil $kategoriMobil): View
    {
        return view('pages.kategori-mobil.edit', compact('kategoriMobil'));
    }

    public function update(UpdateKategoriMobilRequest $request, KategoriMobil $kategoriMobil): RedirectResponse
    {
        $kategoriMobil->update($request->validated());

        return redirect()
            ->route('kategori-mobil.index')
            ->with('success', 'Car category updated successfully.');
    }

    public function destroy(KategoriMobil $kategoriMobil): RedirectResponse
    {
        try {
            $kategoriMobil->delete();
        } catch (QueryException) {
            return redirect()
                ->route('kategori-mobil.index')
                ->with('error', 'Kategori tidak dapat dihapus karena terjadi kendala pada database. Silakan coba kembali.');
        }

        return redirect()
            ->route('kategori-mobil.index')
            ->with('success', 'Car category deleted successfully.');
    }
}
