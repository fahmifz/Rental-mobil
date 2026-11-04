@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail Refund" />

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400" role="status">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/15 dark:text-error-400" role="alert">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    @php
        $reservasi = $refund->reservasi;
        $totalPembayaran = round((float) $reservasi->pembayarans->sum('nominal_pembayaran'), 2);
        $sudahDikembalikan = $refund->status === 'dikembalikan' ? (float) $refund->nominal_refund : 0;
        $statusColor = match ($refund->status) {
            'menunggu_pengembalian' => 'warning',
            'dikembalikan' => 'success',
            'tidak_ada_refund' => 'light',
        };
    @endphp

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <x-common.component-card title="Ringkasan Refund" desc="Pembayaran masuk dan refund keluar dicatat terpisah.">
                <dl class="divide-y divide-gray-100 border-y border-gray-100 dark:divide-gray-800 dark:border-gray-800">
                    <div class="flex justify-between gap-4 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Reservasi</dt><dd class="text-end text-theme-sm font-medium text-gray-800 dark:text-white/90">#{{ $reservasi->id }} · {{ $reservasi->mobil->nama_mobil }}</dd></div>
                    <div class="flex justify-between gap-4 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Pelanggan</dt><dd class="text-end text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $reservasi->user->name }} · {{ $reservasi->user->email }}</dd></div>
                    <div class="flex justify-between gap-4 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Pembayaran masuk</dt><dd class="text-end text-theme-sm font-semibold text-gray-900 dark:text-white">Rp{{ number_format($totalPembayaran, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between gap-4 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Nominal refund diputuskan</dt><dd class="text-end text-theme-sm font-semibold text-gray-900 dark:text-white">Rp{{ number_format((float) $refund->nominal_refund, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between gap-4 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Refund sudah dikembalikan</dt><dd class="text-end text-theme-sm font-semibold text-gray-900 dark:text-white">Rp{{ number_format($sudahDikembalikan, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between gap-4 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Saldo pembayaran bersih</dt><dd class="text-end text-theme-sm font-semibold text-brand-700 dark:text-brand-300">Rp{{ number_format($totalPembayaran - $sudahDikembalikan, 0, ',', '.') }}</dd></div>
                </dl>
            </x-common.component-card>

            <x-common.component-card title="Keputusan Refund" desc="Keputusan ini disimpan ketika reservasi dibatalkan.">
                <dl class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div class="flex justify-between gap-4 py-3"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Jenis</dt><dd class="text-end text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ str_replace('_', ' ', $refund->jenis_refund) }}</dd></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Status</dt><dd><x-ui.badge :color="$statusColor">{{ str_replace('_', ' ', $refund->status) }}</x-ui.badge></dd></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Diputuskan</dt><dd class="text-end text-theme-sm text-gray-800 dark:text-white/90">{{ $refund->diputuskan_pada?->format('d M Y H:i') ?? '-' }} · {{ $refund->diputuskanOleh?->name ?? 'Tidak tersedia' }}</dd></div>
                    <div class="py-3"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Alasan</dt><dd class="mt-1 whitespace-pre-line text-theme-sm text-gray-800 dark:text-white/90">{{ $refund->alasan }}</dd></div>
                </dl>
            </x-common.component-card>
        </div>

        <div class="space-y-6">
            @if ($refund->status === 'menunggu_pengembalian')
                <x-common.component-card title="Konfirmasi Pengembalian Dana" desc="Catat setelah dana benar-benar dikembalikan kepada pelanggan.">
                    <form action="{{ route('admin.refund.confirm', $refund) }}" method="POST" class="space-y-4" onsubmit="return confirm('Konfirmasi bahwa dana refund telah dikembalikan?')">
                        @csrf
                        <div>
                            <label for="metode_refund" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">Metode refund</label>
                            <select id="metode_refund" name="metode_refund" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="">Pilih metode</option>
                                <option value="transfer" @selected(old('metode_refund') === 'transfer')>Transfer</option>
                                <option value="tunai" @selected(old('metode_refund') === 'tunai')>Tunai</option>
                            </select>
                            @error('metode_refund')<p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="tanggal_refund" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">Tanggal pengembalian aktual</label>
                            <input id="tanggal_refund" type="datetime-local" name="tanggal_refund" value="{{ old('tanggal_refund', now()->format('Y-m-d\\TH:i')) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            @error('tanggal_refund')<p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="referensi_refund" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">Referensi/bukti transfer</label>
                            <input id="referensi_refund" type="text" name="referensi_refund" value="{{ old('referensi_refund') }}" maxlength="100" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="Wajib untuk transfer">
                            @error('referensi_refund')<p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white transition hover:bg-brand-600">Konfirmasi Refund</button>
                    </form>
                </x-common.component-card>
            @else
                <x-common.component-card title="Konfirmasi Pengembalian Dana" desc="Status pengembalian dana.">
                    @if ($refund->status === 'dikembalikan')
                        <p class="text-theme-sm text-success-700 dark:text-success-400">Dana telah dikonfirmasi dikembalikan.</p>
                        <dl class="mt-4 divide-y divide-gray-100 dark:divide-gray-800">
                            <div class="flex justify-between gap-3 py-3"><dt class="text-theme-xs text-gray-500 dark:text-gray-400">Metode</dt><dd class="text-end text-theme-sm text-gray-800 dark:text-white/90">{{ ucfirst($refund->metode_refund) }}</dd></div>
                            <div class="flex justify-between gap-3 py-3"><dt class="text-theme-xs text-gray-500 dark:text-gray-400">Tanggal</dt><dd class="text-end text-theme-sm text-gray-800 dark:text-white/90">{{ $refund->tanggal_refund?->format('d M Y H:i') }}</dd></div>
                            <div class="flex justify-between gap-3 py-3"><dt class="text-theme-xs text-gray-500 dark:text-gray-400">Dikonfirmasi oleh</dt><dd class="text-end text-theme-sm text-gray-800 dark:text-white/90">{{ $refund->dikonfirmasiOleh?->name ?? 'Tidak tersedia' }}</dd></div>
                            <div class="py-3"><dt class="text-theme-xs text-gray-500 dark:text-gray-400">Referensi</dt><dd class="mt-1 break-all text-theme-sm text-gray-800 dark:text-white/90">{{ $refund->referensi_refund ?: '-' }}</dd></div>
                        </dl>
                    @else
                        <p class="text-theme-sm text-gray-600 dark:text-gray-400">Keputusan ini tidak memiliki pengembalian dana.</p>
                    @endif
                </x-common.component-card>
            @endif

            <a href="{{ route('admin.refund.index') }}" class="inline-flex w-full items-center justify-center rounded-lg bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">Kembali ke Daftar Refund</a>
        </div>
    </div>
@endsection
