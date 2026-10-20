@php
    $mobil = $mobil ?? null;
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800';
    $textareaClass = 'w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800';
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <label for="nama_mobil" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Mobil <span class="text-error-500">*</span></label>
            <input id="nama_mobil" name="nama_mobil" type="text" value="{{ old('nama_mobil', $mobil?->nama_mobil) }}" required autofocus class="{{ $inputClass }}">
            @error('nama_mobil') <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="merek" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Merek <span class="text-error-500">*</span></label>
            <input id="merek" name="merek" type="text" value="{{ old('merek', $mobil?->merek) }}" required class="{{ $inputClass }}">
            @error('merek') <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="tipe" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tipe</label>
            <input id="tipe" name="tipe" type="text" value="{{ old('tipe', $mobil?->tipe) }}" class="{{ $inputClass }}">
            @error('tipe') <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="plat_nomor" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Plat Nomor <span class="text-error-500">*</span></label>
            <input id="plat_nomor" name="plat_nomor" type="text" value="{{ old('plat_nomor', $mobil?->plat_nomor) }}" required class="{{ $inputClass }}">
            @error('plat_nomor') <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="kategori_mobil_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kategori</label>
            <select id="kategori_mobil_id" name="kategori_mobil_id" class="{{ $inputClass }}">
                <option value="">Tanpa kategori</option>
                @foreach ($kategoriMobils as $kategoriMobil)
                    <option value="{{ $kategoriMobil->id }}" @selected(old('kategori_mobil_id', $mobil?->kategori_mobil_id) == $kategoriMobil->id)>{{ $kategoriMobil->nama_kategori }}</option>
                @endforeach
            </select>
            @error('kategori_mobil_id') <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status <span class="text-error-500">*</span></label>
            <select id="status" name="status" required class="{{ $inputClass }}">
                @foreach (['tersedia' => 'Tersedia', 'disewa' => 'Disewa', 'perawatan' => 'Perawatan'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $mobil?->status ?? 'tersedia') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="tahun" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun</label>
            <input id="tahun" name="tahun" type="number" min="1900" max="{{ now()->year + 1 }}" value="{{ old('tahun', $mobil?->tahun) }}" class="{{ $inputClass }}">
            @error('tahun') <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="transmisi" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Transmisi</label>
            <input id="transmisi" name="transmisi" type="text" value="{{ old('transmisi', $mobil?->transmisi) }}" placeholder="Contoh: Manual, Automatic, CVT" class="{{ $inputClass }}">
            @error('transmisi') <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="kapasitas_penumpang" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kapasitas Penumpang <span class="text-error-500">*</span></label>
            <input id="kapasitas_penumpang" name="kapasitas_penumpang" type="number" min="1" max="100" value="{{ old('kapasitas_penumpang', $mobil?->kapasitas_penumpang ?? 5) }}" required class="{{ $inputClass }}">
            @error('kapasitas_penumpang') <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="harga_sewa_per_hari" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tarif Sewa per Hari <span class="text-error-500">*</span></label>
            <input id="harga_sewa_per_hari" name="harga_sewa_per_hari" type="number" min="0" step="0.01" value="{{ old('harga_sewa_per_hari', $mobil?->harga_sewa_per_hari) }}" required class="{{ $inputClass }}">
            @error('harga_sewa_per_hari') <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label for="foto" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Foto Mobil</label>
        @if ($mobil?->foto)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($mobil->foto) }}" alt="{{ $mobil->nama_mobil }}" class="mb-3 h-24 w-36 rounded-lg object-cover">
        @endif
        <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" class="h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 shadow-theme-xs ltr:file:me-5 rtl:file:ms-5 file:cursor-pointer file:border-0 ltr:file:border-e rtl:file:border-s file:border-solid file:border-gray-200 file:bg-gray-50 file:px-3.5 file:py-3 file:text-sm file:text-gray-700 hover:file:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400">
        <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.</p>
        @error('foto') <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="deskripsi" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" rows="5" class="{{ $textareaClass }}">{{ old('deskripsi', $mobil?->deskripsi) }}</textarea>
        @error('deskripsi') <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p> @enderror
    </div>

    <div class="flex flex-wrap items-center justify-end gap-3">
        <a href="{{ route('mobil.index') }}" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">Batal</a>
        <x-ui.button type="submit" size="sm">{{ $submitLabel }}</x-ui.button>
    </div>
</form>
