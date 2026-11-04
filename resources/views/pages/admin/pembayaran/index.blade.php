@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pembayaran" />

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/15 dark:text-error-400" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/15 dark:text-error-400" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <x-common.component-card title="Daftar Pembayaran" desc="Catatan pembayaran manual untuk reservasi yang disetujui atau telah selesai.">
        <form action="{{ route('admin.pembayaran.index') }}" method="GET" class="mb-5 flex w-full max-w-md gap-2">
            <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama atau email pelanggan"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
            <button type="submit" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-gray-100 px-4 py-2.5 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-white/[0.05] dark:text-gray-300 dark:hover:bg-white/[0.1]">Cari</button>
        </form>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[1280px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Reservasi</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Pelanggan</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Mobil</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Biaya sewa</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Denda</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Total tagihan</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Sudah dibayar</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p></th>
                            <th class="px-5 py-3 text-end sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Aksi</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reservasis as $reservasi)
                            @php
                                $dibayar = $reservasi->totalPaid();
                                $biayaSewa = (float) $reservasi->total_biaya;
                                $denda = $reservasi->lateFeeAmount();
                                $total = $reservasi->totalAmountDue();
                                $paymentStatus = $reservasi->paymentStatus();
                                $statusColor = $paymentStatus === 'lunas'
                                    ? 'success'
                                    : ($paymentStatus === 'dibayar_sebagian' ? 'warning' : 'error');
                            @endphp
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="font-medium text-theme-sm text-gray-800 dark:text-white/90">#{{ $reservasi->id }}</p>
                                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $reservasi->status)) }}</p>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="font-medium text-theme-sm text-gray-800 dark:text-white/90">{{ $reservasi->user->name }}</p>
                                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $reservasi->user->email }}</p>
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ $reservasi->mobil->nama_mobil }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">Rp{{ number_format($biayaSewa, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">Rp{{ number_format($denda, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">Rp{{ number_format($total, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">Rp{{ number_format($dibayar, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    <x-ui.badge :color="$statusColor">{{ str_replace('_', ' ', ucfirst($paymentStatus)) }}</x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-end sm:px-6">
                                    @if ($reservasi->pembayarans->isNotEmpty())
                                        <a href="{{ route('admin.pembayaran.show', $reservasi) }}" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-brand-500/15">Detail</a>
                                    @else
                                        <a href="{{ route('admin.pembayaran.create', $reservasi) }}" class="inline-flex rounded-lg bg-brand-500 px-3 py-2 text-theme-sm font-medium text-white transition hover:bg-brand-600">Catat</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-10 text-center text-theme-sm text-gray-500 dark:text-gray-400">Belum ada reservasi yang dapat dibayar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($reservasis->hasPages())
            <div class="pt-2">{{ $reservasis->links() }}</div>
        @endif
    </x-common.component-card>
@endsection
