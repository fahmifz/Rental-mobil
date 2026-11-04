@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Keputusan Refund" />

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/15 dark:text-error-400" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <x-common.component-card title="Batalkan Reservasi dan Tentukan Refund" desc="Reservasi berbayar harus memiliki keputusan refund sebelum pembatalan disimpan.">
                <form action="{{ route('admin.reservasi.cancel', $reservasi) }}" method="POST"
                    x-data="{ jenis: @js(old('jenis_refund', 'penuh')), total: @js($totalPembayaran), nominal: @js(old('nominal_refund', number_format($totalPembayaran, 2, '.', ''))) }"
                    class="space-y-6" onsubmit="return confirm('Simpan pembatalan dan keputusan refund ini?')">
                    @csrf

                    <div>
                        <label for="alasan_pembatalan" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">Alasan pembatalan <span class="font-normal text-gray-500 dark:text-gray-400">(opsional)</span></label>
                        <textarea id="alasan_pembatalan" name="alasan_pembatalan" rows="3" maxlength="1000"
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                            placeholder="Contoh: permintaan pelanggan atau kendaraan tidak tersedia">{{ old('alasan_pembatalan') }}</textarea>
                        @error('alasan_pembatalan')<p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <fieldset>
                        <legend class="mb-3 text-theme-sm font-medium text-gray-700 dark:text-gray-300">Keputusan refund</legend>
                        <div class="grid gap-3 md:grid-cols-3">
                            @foreach (['penuh' => 'Refund penuh', 'sebagian' => 'Refund sebagian', 'tidak_ada' => 'Tidak ada refund'] as $value => $label)
                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4 transition hover:border-brand-300 dark:border-gray-800 dark:hover:border-brand-700" :class="jenis === '{{ $value }}' ? 'border-brand-500 bg-brand-50 dark:border-brand-500 dark:bg-brand-500/10' : ''">
                                    <input type="radio" name="jenis_refund" value="{{ $value }}" x-model="jenis"
                                        @change="nominal = jenis === 'penuh' ? total : (jenis === 'tidak_ada' ? 0 : '')"
                                        class="mt-0.5 h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500" @checked(old('jenis_refund', 'penuh') === $value)>
                                    <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('jenis_refund')<p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>@enderror
                    </fieldset>

                    <div>
                        <label for="nominal_refund" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">Nominal refund</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-theme-sm text-gray-500 dark:text-gray-400">Rp</span>
                            <input id="nominal_refund" name="nominal_refund" type="number" min="0" step="0.01" x-model="nominal"
                                :readonly="jenis !== 'sebagian'"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-4 ps-11 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 read-only:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:read-only:bg-white/[0.03] dark:focus:border-brand-800">
                        </div>
                        <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">Pembayaran yang diterima: Rp{{ number_format($totalPembayaran, 0, ',', '.') }}. Nominal refund sebagian harus lebih kecil dari jumlah tersebut.</p>
                        @error('nominal_refund')<p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="alasan_refund" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">Alasan keputusan refund</label>
                        <textarea id="alasan_refund" name="alasan_refund" rows="4" maxlength="1000" required
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                            placeholder="Jelaskan alasan nominal refund yang diputuskan">{{ old('alasan_refund') }}</textarea>
                        @error('alasan_refund')<p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                        <a href="{{ route('admin.reservasi.index') }}" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">Kembali</a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-error-500 px-5 py-2.5 text-theme-sm font-medium text-white transition hover:bg-error-600">Simpan Pembatalan</button>
                    </div>
                </form>
            </x-common.component-card>
        </div>

        <x-common.component-card title="Ringkasan Reservasi" desc="Pembayaran lama tidak akan diubah.">
            <dl class="divide-y divide-gray-100 dark:divide-gray-800">
                <div class="py-3"><dt class="text-theme-xs text-gray-500 dark:text-gray-400">Reservasi</dt><dd class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">#{{ $reservasi->id }}</dd></div>
                <div class="py-3"><dt class="text-theme-xs text-gray-500 dark:text-gray-400">Pelanggan</dt><dd class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $reservasi->user->name }}</dd></div>
                <div class="py-3"><dt class="text-theme-xs text-gray-500 dark:text-gray-400">Mobil</dt><dd class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $reservasi->mobil->nama_mobil }}</dd></div>
                <div class="py-3"><dt class="text-theme-xs text-gray-500 dark:text-gray-400">Total pembayaran masuk</dt><dd class="mt-1 text-theme-sm font-semibold text-gray-900 dark:text-white">Rp{{ number_format($totalPembayaran, 0, ',', '.') }}</dd></div>
            </dl>
        </x-common.component-card>
    </div>
@endsection
