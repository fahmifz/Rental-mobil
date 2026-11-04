@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pelanggan" />

    <x-common.component-card title="Daftar Pelanggan" desc="Data akun pelanggan dan jumlah reservasi yang telah dibuat.">
        <form action="{{ route('admin.pelanggan.index') }}" method="GET" class="mb-5 flex w-full max-w-md gap-2">
            <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama atau email pelanggan"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
            <button type="submit" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-gray-100 px-4 py-2.5 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-white/[0.05] dark:text-gray-300 dark:hover:bg-white/[0.1]">Cari</button>
        </form>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[860px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Pelanggan</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Email</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Jumlah reservasi</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Terdaftar</p></th>
                            <th class="px-5 py-3 text-end sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Aksi</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pelanggans as $pelanggan)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                <td class="px-5 py-4 sm:px-6"><p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $pelanggan->name }}</p></td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $pelanggan->email }}</td>
                                <td class="px-5 py-4"><x-ui.badge color="primary">{{ $pelanggan->reservasis_count }} reservasi</x-ui.badge></td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $pelanggan->created_at?->format('d M Y') ?? '-' }}</td>
                                <td class="px-5 py-4 text-end sm:px-6"><a href="{{ route('admin.pelanggan.show', $pelanggan) }}" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-brand-500/15">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-theme-sm text-gray-500 dark:text-gray-400">Pelanggan tidak ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($pelanggans->hasPages())
            <div class="pt-4">{{ $pelanggans->links() }}</div>
        @endif
    </x-common.component-card>
@endsection
