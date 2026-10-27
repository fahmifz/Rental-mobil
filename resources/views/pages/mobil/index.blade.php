@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Data Mobil" />

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <x-common.component-card title="Data Mobil" desc="Kelola armada mobil yang tersedia untuk disewakan.">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form action="{{ route('mobil.index') }}" method="GET" class="flex w-full max-w-md gap-2">
                <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama, merek, atau plat nomor"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                <button type="submit" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-white/[0.05] dark:text-gray-300 dark:hover:bg-white/[0.1]">
                    Cari
                </button>
            </form>

            <a href="{{ route('mobil.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                Tambah Mobil
            </a>
        </div>

        @if ($search !== '')
            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:bg-white/[0.03] dark:text-gray-400">
                <span>Hasil pencarian untuk “{{ $search }}”</span>
                <a href="{{ route('mobil.index') }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">Bersihkan</a>
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[980px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Foto</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Mobil</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Plat Nomor</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Kategori</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Tarif / Hari</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p></th>
                            <th class="px-5 py-3 text-end sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Aksi</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mobils as $mobil)
                            @php
                                $statusColor = match ($mobil->status) {
                                    'tersedia' => 'primary',
                                    'disewa' => 'warning',
                                    'perawatan' => 'error',
                                };
                            @endphp
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                <td class="px-5 py-4 sm:px-6">
                                    @if ($mobil->foto)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($mobil->foto) }}" alt="{{ $mobil->nama_mobil }}" class="size-12 rounded-lg object-cover">
                                    @else
                                        <div class="flex size-12 items-center justify-center rounded-lg bg-gray-100 text-theme-xs text-gray-500 dark:bg-gray-800 dark:text-gray-400">No photo</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $mobil->nama_mobil }}</p>
                                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $mobil->merek }}{{ $mobil->tipe ? ' · ' . $mobil->tipe : '' }}</p>
                                </td>
                                <td class="px-5 py-4 sm:px-6"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $mobil->plat_nomor }}</p></td>
                                <td class="px-5 py-4 sm:px-6"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $mobil->kategori?->nama_kategori ?? 'Tanpa kategori' }}</p></td>
                                <td class="px-5 py-4 sm:px-6"><p class="text-gray-500 text-theme-sm dark:text-gray-400">Rp{{ number_format((float) $mobil->harga_sewa_per_hari, 0, ',', '.') }}</p></td>
                                <td class="px-5 py-4 sm:px-6"><x-ui.badge :color="$statusColor">{{ ucfirst($mobil->status) }}</x-ui.badge></td>
                                <td class="px-5 py-4 text-end sm:px-6">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('mobil.edit', $mobil) }}" class="rounded-lg px-3 py-2 text-sm font-medium text-brand-600 transition hover:bg-brand-50 hover:text-brand-700 dark:text-brand-300 dark:hover:bg-brand-500/15 dark:hover:text-brand-200">Edit</a>
                                        <form action="{{ route('mobil.destroy', $mobil) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg px-3 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/15">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada data mobil.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($mobils->hasPages())
            <div class="pt-2">{{ $mobils->links() }}</div>
        @endif
    </x-common.component-card>
@endsection
