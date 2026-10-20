<form action="{{ $action }}" method="POST" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="nama_kategori" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Category Name <span class="text-error-500">*</span>
        </label>
        <input id="nama_kategori" name="nama_kategori" type="text"
            value="{{ old('nama_kategori', $kategoriMobil->nama_kategori ?? '') }}" required autofocus
            class="h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 {{ $errors->has('nama_kategori') ? 'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-500' : 'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-800' }}">
        @error('nama_kategori')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="deskripsi" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Description
        </label>
        <textarea id="deskripsi" name="deskripsi" rows="5"
            class="w-full rounded-lg border bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 {{ $errors->has('deskripsi') ? 'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-500' : 'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-800' }}">{{ old('deskripsi', $kategoriMobil->deskripsi ?? '') }}</textarea>
        @error('deskripsi')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-wrap items-center justify-end gap-3">
        <a href="{{ route('kategori-mobil.index') }}"
            class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
            Cancel
        </a>
        <x-ui.button type="submit" size="sm">{{ $submitLabel }}</x-ui.button>
    </div>
</form>
