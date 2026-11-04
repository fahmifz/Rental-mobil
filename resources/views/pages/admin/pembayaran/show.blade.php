@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail Pembayaran" />

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400" role="alert">{{ session('success') }}</div>
    @endif

    <x-common.component-card title="Pembayaran Reservasi #{{ $reservasi->id }}" desc="Ringkasan tagihan dan riwayat transaksi pembayaran.">
        @php
            $dibayar = $reservasi->totalPaid();
            $biayaSewa = (float) $reservasi->total_biaya;
            $denda = $reservasi->lateFeeAmount();
            $total = $reservasi->totalAmountDue();
            $sisa = $reservasi->remainingAmount();
            $status = $reservasi->paymentStatus();
        @endphp
        <dl class="divide-y divide-gray-100 border-y border-gray-100 dark:divide-gray-800 dark:border-gray-800">
            <div class="flex justify-between gap-3 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Pelanggan</dt><dd class="text-end text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $reservasi->user->name }} · {{ $reservasi->user->email }}</dd></div>
            <div class="flex justify-between gap-3 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Mobil</dt><dd class="text-end text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $reservasi->mobil->nama_mobil }}</dd></div>
            <div class="flex justify-between gap-3 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Biaya sewa</dt><dd class="text-end text-theme-sm font-medium text-gray-800 dark:text-white/90">Rp{{ number_format($biayaSewa, 0, ',', '.') }}</dd></div>
            <div class="flex justify-between gap-3 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Denda keterlambatan</dt><dd class="text-end text-theme-sm font-medium text-gray-800 dark:text-white/90">Rp{{ number_format($denda, 0, ',', '.') }}</dd></div>
            <div class="flex justify-between gap-3 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Total tagihan</dt><dd class="text-end text-theme-sm font-semibold text-gray-900 dark:text-white">Rp{{ number_format($total, 0, ',', '.') }}</dd></div>
            <div class="flex justify-between gap-3 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Sudah dibayar</dt><dd class="text-end text-theme-sm font-semibold text-gray-900 dark:text-white">Rp{{ number_format($dibayar, 0, ',', '.') }}</dd></div>
            <div class="flex justify-between gap-3 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Sisa tagihan</dt><dd class="text-end text-theme-sm font-semibold text-brand-700 dark:text-brand-300">Rp{{ number_format($sisa, 0, ',', '.') }}</dd></div>
            <div class="flex justify-between gap-3 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Status</dt><dd><x-ui.badge :color="$status === 'lunas' ? 'success' : ($status === 'dibayar_sebagian' ? 'warning' : 'error')">{{ str_replace('_', ' ', ucfirst($status)) }}</x-ui.badge></dd></div>
        </dl>

        <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
            <div class="max-w-full overflow-x-auto">
                <table class="w-full min-w-[720px]">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Tanggal</th>
                        <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Nominal</th>
                        <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Metode</th>
                        <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Dicatat oleh</th>
                        <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Catatan</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($reservasi->pembayarans as $transaksi)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                <td class="px-4 py-3 text-theme-sm text-gray-700 dark:text-gray-300">{{ $transaksi->tanggal_pembayaran->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-theme-sm font-medium text-gray-800 dark:text-white/90">Rp{{ number_format((float) $transaksi->nominal_pembayaran, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-theme-sm text-gray-700 dark:text-gray-300">{{ ucfirst($transaksi->metode_pembayaran) }}</td>
                                <td class="px-4 py-3 text-theme-sm text-gray-700 dark:text-gray-300">{{ $transaksi->dicatatOleh?->name ?? 'Tidak tersedia' }}</td>
                                <td class="px-4 py-3 text-theme-sm text-gray-700 dark:text-gray-300">{{ $transaksi->catatan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">Belum ada transaksi pembayaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap justify-end gap-3">
            <a href="{{ route('admin.pembayaran.index') }}" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">Kembali</a>
            @if ($sisa > 0 && in_array($reservasi->status, ['disetujui', 'selesai'], true))
                <a href="{{ route('admin.pembayaran.edit', $reservasi) }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white transition hover:bg-brand-600">Tambah Pembayaran</a>
            @elseif ($reservasi->pembayaran && $sisa <= 0)
                <span class="inline-flex rounded-lg bg-success-50 px-4 py-2.5 text-theme-sm font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">Lunas</span>
            @else
                <a href="{{ route('admin.pembayaran.create', $reservasi) }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white transition hover:bg-brand-600">Catat Pembayaran</a>
            @endif
        </div>
    </x-common.component-card>
@endsection
