@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Tambah Pembayaran" />

    <x-common.component-card title="Tambah Pembayaran Reservasi #{{ $reservasi->id }}" desc="Masukkan nominal transaksi pembayaran saat ini.">
        @php
            $dibayar = $reservasi->totalPaid();
            $total = (float) $reservasi->total_biaya;
        @endphp
        <div class="grid gap-6 lg:grid-cols-2">
            <dl class="rounded-xl border border-gray-200 p-5 dark:border-gray-800">
                <div class="flex justify-between gap-3 py-2"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Total biaya</dt><dd class="text-end text-theme-sm font-semibold text-gray-900 dark:text-white">Rp{{ number_format($total, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between gap-3 py-2"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Sudah tercatat</dt><dd class="text-end text-theme-sm font-medium text-gray-800 dark:text-white/90">Rp{{ number_format($dibayar, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between gap-3 py-2"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Sisa tagihan</dt><dd class="text-end text-theme-sm font-medium text-brand-700 dark:text-brand-300">Rp{{ number_format(max(0, $total - $dibayar), 0, ',', '.') }}</dd></div>
            </dl>

            <form action="{{ route('admin.pembayaran.store', $reservasi) }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">
                <div>
                    <label for="nominal_pembayaran" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">Nominal pembayaran saat ini</label>
                    <input id="nominal_pembayaran" name="nominal_pembayaran" type="number" step="0.01" min="0.01" max="{{ max(0, $total - $dibayar) }}" value="{{ old('nominal_pembayaran') }}" required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    @error('nominal_pembayaran')<p class="mt-1.5 text-theme-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tanggal_pembayaran" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">Tanggal pembayaran terakhir</label>
                    <input id="tanggal_pembayaran" name="tanggal_pembayaran" type="date" value="{{ old('tanggal_pembayaran', today()->toDateString()) }}" required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    @error('tanggal_pembayaran')<p class="mt-1.5 text-theme-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="metode_pembayaran" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">Metode pembayaran</label>
                    <select id="metode_pembayaran" name="metode_pembayaran" required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="tunai" @selected(old('metode_pembayaran') === 'tunai')>Tunai</option>
                        <option value="transfer" @selected(old('metode_pembayaran') === 'transfer')>Transfer</option>
                    </select>
                    @error('metode_pembayaran')<p class="mt-1.5 text-theme-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="catatan" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                    <textarea id="catatan" name="catatan" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('catatan') }}</textarea>
                    @error('catatan')<p class="mt-1.5 text-theme-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div class="flex flex-wrap justify-end gap-3">
                    <a href="{{ route('admin.pembayaran.show', $reservasi) }}" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">Batal</a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white transition hover:bg-brand-600">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </x-common.component-card>
@endsection
