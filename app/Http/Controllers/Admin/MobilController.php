<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMobilRequest;
use App\Http\Requests\UpdateMobilRequest;
use App\Models\KategoriMobil;
use App\Models\Mobil;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class MobilController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $mobils = Mobil::query()
            ->with('kategori')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nama_mobil', 'like', "%{$search}%")
                        ->orWhere('merek', 'like', "%{$search}%")
                        ->orWhere('plat_nomor', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.mobil.index', compact('mobils', 'search'));
    }

    public function create(): View
    {
        return view('pages.mobil.create', [
            'kategoriMobils' => $this->kategoriMobils(),
        ]);
    }

    public function store(StoreMobilRequest $request): RedirectResponse
    {
        $data = Arr::except($request->validated(), ['foto']);
        $photo = null;

        if ($request->hasFile('foto')) {
            $photo = $request->file('foto')->store('mobil', 'public');
            $data['foto'] = $photo;
        }

        try {
            Mobil::create($data);
        } catch (Throwable $exception) {
            $this->deletePhoto($photo);

            throw $exception;
        }

        return redirect()
            ->route('mobil.index')
            ->with('success', 'Mobil berhasil ditambahkan.');
    }

    public function edit(Mobil $mobil): View
    {
        return view('pages.mobil.edit', [
            'mobil' => $mobil,
            'kategoriMobils' => $this->kategoriMobils(),
        ]);
    }

    public function update(UpdateMobilRequest $request, Mobil $mobil): RedirectResponse
    {
        $data = Arr::except($request->validated(), ['foto']);
        $newPhoto = null;

        if ($request->hasFile('foto')) {
            $newPhoto = $request->file('foto')->store('mobil', 'public');
            $data['foto'] = $newPhoto;
        }

        $oldPhoto = $mobil->foto;

        try {
            $mobil->update($data);
        } catch (Throwable $exception) {
            $this->deletePhoto($newPhoto);

            throw $exception;
        }

        if ($newPhoto !== null) {
            $this->deletePhoto($oldPhoto);
        }

        return redirect()
            ->route('mobil.index')
            ->with('success', 'Mobil berhasil diperbarui.');
    }

    public function destroy(Mobil $mobil): RedirectResponse
    {
        $photo = $mobil->foto;

        if ($mobil->delete()) {
            $this->deletePhoto($photo);
        }

        return redirect()
            ->route('mobil.index')
            ->with('success', 'Mobil berhasil dihapus.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, KategoriMobil>
     */
    private function kategoriMobils()
    {
        return KategoriMobil::query()
            ->orderBy('nama_kategori')
            ->get();
    }

    private function deletePhoto(?string $photo): void
    {
        if ($photo !== null && Storage::disk('public')->exists($photo)) {
            Storage::disk('public')->delete($photo);
        }
    }
}
