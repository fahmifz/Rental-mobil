@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Refund" />

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/15 dark:text-error-400" role="alert">{{ session('error') }}</div>
    @endif

    <x-common.component-card title="Daftar Refund" desc="Keputusan refund dan status pengembalian dana reservasi yang dibatalkan.">
        <form action="{{ route('admin.refund.index') }}" method="GET" class="mb-5 flex flex-wrap items-end gap-3">
            <div>
                <label for="status" class="mb-1.5 block text-theme-xs font-medium text-gray-500 dark:text-gray-400">Status refund</label>
                <select id="status" name="status" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Semua status</option>
                    <option value="menunggu_pengembalian" @selected($status === 'menunggu_pengembalian')>Menunggu pengembalian</option>
                    <option value="dikembalikan" @selected($status === 'dikembalikan')>Dikembalikan</option>
                    <option value="tidak_ada_refund" @selected($status === 'tidak_ada_refund')>Tidak ada refund</option>
                </select>
            </div>
            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-gray-100 px-4 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-white/[0.05] dark:text-gray-300 dark:hover:bg-white/[0.1]">Filter</button>
        </form>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[1120px]">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800">
                        @foreach (['Reservasi', 'Pelanggan', 'Pembayaran masuk', 'Nominal refund', 'Status', 'Keputusan', 'Aksi'] as $heading)
                            <th class="px-5 py-3 {{ $heading === 'Aksi' ? 'text-end' : 'text-start' }} sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">{{ $heading }}</p></th>
                        @endforeach
                    </tr></thead>
                    <tbody>
                        @forelse ($refunds as $refund)
                            @php
                                $totalPembayaran = round((float) $refund->reservasi->pembayarans->sum('nominal_pembayaran'), 2);
                                $statusColor = match ($refund->status) {
                                    'menunggu_pengembalian' => 'warning',
                                    'dikembalikan' => 'success',
                                    'tidak_ada_refund' => 'light',
                                };
                            @endphp
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">#{{ $refund->reservasi_id }}</td>
                                <td class="px-5 py-4"><p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $refund->reservasi->user->name }}</p><p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $refund->reservasi->mobil->nama_mobil }}</p></td>
                                <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">Rp{{ number_format($totalPembayaran, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">Rp{{ number_format((float) $refund->nominal_refund, 0, ',', '.') }}</td>
                                <td class="px-5 py-4"><x-ui.badge :color="$statusColor">{{ str_replace('_', ' ', $refund->status) }}</x-ui.badge></td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $refund->diputuskan_pada?->format('d M Y H:i') ?? '-' }}</td>
                                <td class="px-5 py-4 text-end sm:px-6"><a href="{{ route('admin.refund.show', $refund) }}" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-brand-500/15">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-theme-sm text-gray-500 dark:text-gray-400">Belum ada keputusan refund.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($refunds->hasPages())
            <div class="pt-2">{{ $refunds->links() }}</div>
        @endif
    </x-common.component-card>
@endsection
