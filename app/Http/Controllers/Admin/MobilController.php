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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
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
        $data = Arr::except($request->validated(), ['foto', 'status']);
        $requestedStatus = (string) $request->validated()['status'];
        $newPhoto = null;

        if ($request->hasFile('foto')) {
            $newPhoto = $request->file('foto')->store('mobil', 'public');
            $data['foto'] = $newPhoto;
        }

        $oldPhoto = $mobil->foto;

        try {
            DB::transaction(function () use ($data, $requestedStatus, $newPhoto, $mobil): void {
                $lockedMobil = Mobil::query()
                    ->lockForUpdate()
                    ->findOrFail($mobil->id);

                $lockedMobil->update($data);

                if ($requestedStatus === 'perawatan') {
                    $lockedMobil->update(['status' => 'perawatan']);
                    return;
                }

                $lockedMobil->syncOperationalStatus(today());
            });
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
        $errorMessage = null;

        try {
            DB::transaction(function () use ($mobil, &$errorMessage): void {
                $lockedMobil = Mobil::query()
                    ->lockForUpdate()
                    ->findOrFail($mobil->id);

                if ($lockedMobil->ratings()->exists()) {
                    $errorMessage = 'Mobil tidak dapat dihapus karena masih memiliki rating terkait.';
                    return;
                }

                if ($lockedMobil->reservasis()->exists()) {
                    $errorMessage = 'Mobil tidak dapat dihapus karena masih memiliki reservasi terkait.';
                    return;
                }

                $lockedMobil->delete();
            });
        } catch (QueryException) {
            return redirect()
                ->route('mobil.index')
                ->with('error', 'Mobil tidak dapat dihapus karena masih digunakan oleh reservasi atau rating.');
        }

        if ($errorMessage !== null) {
            return redirect()
                ->route('mobil.index')
                ->with('error', $errorMessage);
        }

        $this->deletePhoto($photo);

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
