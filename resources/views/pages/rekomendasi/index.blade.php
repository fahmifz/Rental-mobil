@extends('layouts.landing')

@section('content')
    <main class="min-h-screen bg-gray-50 px-4 py-10 font-outfit dark:bg-gray-900 sm:px-6">
        <section class="mx-auto w-full max-w-5xl">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <a href="{{ route('account') }}" class="text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">Kembali ke akun</a>
                    <h1 class="mt-3 text-title-sm font-semibold text-gray-900 dark:text-white">Rekomendasi Mobil</h1>
                    <p class="mt-2 text-theme-sm text-gray-600 dark:text-gray-300">Pilihan diurutkan berdasarkan hasil perhitungan User-Based Collaborative Filtering.</p>
                </div>
                @if (count($recommendations) > 0)
                    <x-ui.badge color="primary">{{ count($recommendations) }} mobil tersedia</x-ui.badge>
                @endif
            </div>

            @if (count($recommendations) === 0)
                <div class="mt-8 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center dark:border-gray-700 dark:bg-white/[0.03]">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                        <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M3 11h18M5 11l1.5-5h11L19 11M5 11v7a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-7M8 15h8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h2 class="text-theme-xl font-semibold text-gray-800 dark:text-white/90">Belum ada rekomendasi</h2>
                    <p class="mt-2 text-theme-sm text-gray-500 dark:text-gray-400">Rating dan tetangga yang cukup belum tersedia untuk menghasilkan rekomendasi.</p>
                    <a href="{{ route('reservasi.index') }}" class="mt-5 inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Lihat Reservasi Saya</a>
                </div>
            @else
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($recommendations as $index => $recommendation)
                        @php($mobil = $recommendation['mobil'])
                        <article class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-theme-md dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-500/30">
                            <div class="relative aspect-[16/10] bg-gray-100 dark:bg-gray-800">
                                @if ($mobil->foto)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($mobil->foto) }}" alt="{{ $mobil->nama_mobil }}" class="size-full object-cover" loading="lazy">
                                @else
                                    <div class="flex size-full items-center justify-center text-gray-400 dark:text-gray-500">
                                        <svg class="size-12 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 11h18M5 11l1.5-5h11L19 11M5 11v7a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-7M8 15h8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    </div>
                                @endif
                                <div class="absolute start-4 top-4 flex items-center gap-2">
                                    <span class="inline-flex size-9 items-center justify-center rounded-full bg-white text-theme-sm font-semibold text-brand-700 shadow-theme-sm ring-1 ring-gray-200 dark:bg-gray-900 dark:text-brand-300 dark:ring-gray-700" aria-label="Peringkat {{ $index + 1 }}">
                                        {{ $index + 1 }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <h2 class="text-theme-xl font-semibold text-gray-900 dark:text-white">{{ $mobil->nama_mobil }}</h2>
                                        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $mobil->merek }}</p>
                                    </div>
                                    <x-ui.badge color="primary">Tersedia</x-ui.badge>
                                </div>
                                @if ($mobil->deskripsi)
                                    <p class="mt-4 text-theme-sm leading-relaxed text-gray-600 dark:text-gray-300">{{ $mobil->deskripsi }}</p>
                                @endif

                                <dl class="mt-5 grid grid-cols-2 gap-3 border-y border-gray-100 py-4 dark:border-gray-800">
                                    <div>
                                        <dt class="text-theme-xs text-gray-500 dark:text-gray-400">Tarif per hari</dt>
                                        <dd class="mt-1 text-theme-sm font-semibold text-gray-900 dark:text-white">Rp{{ number_format((float) $mobil->harga_sewa_per_hari, 0, ',', '.') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-theme-xs text-gray-500 dark:text-gray-400">Transmisi</dt>
                                        <dd class="mt-1 text-theme-sm font-semibold text-gray-900 dark:text-white">{{ $mobil->transmisi ?: 'Tidak dicatat' }}</dd>
                                    </div>
                                </dl>

                                <div class="mt-4 rounded-xl bg-brand-50 px-4 py-3 dark:bg-brand-500/10">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-theme-xs font-medium text-gray-600 dark:text-gray-300">Skor prediksi</span>
                                        <span class="text-theme-xl font-semibold text-brand-700 dark:text-brand-300">{{ number_format((float) $recommendation['predicted_rating'], 1, ',', '.') }}</span>
                                    </div>
                                </div>

                                <a href="{{ route('reservasi.create') }}" class="mt-4 inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                                    Buat Reservasi
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </main>
@endsection
