@extends('layouts.landing')

@section('content')
    <main class="min-h-screen bg-gray-50 px-4 py-10 dark:bg-gray-900 sm:px-6">
        <section class="mx-auto w-full max-w-5xl">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <a href="{{ route('account') }}" class="text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">Kembali ke akun</a>
                    <h1 class="mt-3 text-title-sm font-semibold text-gray-900 dark:text-white">Rekomendasi Mobil</h1>
                    <p class="mt-2 text-theme-sm text-gray-600 dark:text-gray-300">Pilihan diurutkan berdasarkan hasil perhitungan Item-Based Collaborative Filtering.</p>
                </div>
            </div>

            @if (count($recommendations) === 0)
                <div class="mt-8 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center dark:border-gray-700 dark:bg-white/[0.03]">
                    <h2 class="text-theme-xl font-semibold text-gray-800 dark:text-white/90">Belum ada rekomendasi</h2>
                    <p class="mt-2 text-theme-sm text-gray-500 dark:text-gray-400">Rating dan tetangga yang cukup belum tersedia untuk menghasilkan rekomendasi.</p>
                    <a href="{{ route('reservasi.index') }}" class="mt-5 inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Lihat Reservasi Saya</a>
                </div>
            @else
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($recommendations as $recommendation)
                        @php($mobil = $recommendation['mobil'])
                        <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                            <div class="aspect-[16/10] bg-gray-100 dark:bg-gray-800">
                                @if ($mobil->foto)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($mobil->foto) }}" alt="{{ $mobil->nama_mobil }}" class="size-full object-cover" loading="lazy">
                                @else
                                    <div class="flex size-full items-center justify-center text-gray-400 dark:text-gray-500">
                                        <svg class="size-12 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 11h18M5 11l1.5-5h11L19 11M5 11v7a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-7M8 15h8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h2 class="text-theme-xl font-semibold text-gray-900 dark:text-white">{{ $mobil->nama_mobil }}</h2>
                                        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $mobil->merek }}</p>
                                    </div>
                                    <x-ui.badge color="primary">Tersedia</x-ui.badge>
                                </div>
                                @if ($mobil->deskripsi)
                                    <p class="mt-4 text-theme-sm leading-relaxed text-gray-600 dark:text-gray-300">{{ $mobil->deskripsi }}</p>
                                @endif
                                <div class="mt-5 border-t border-gray-100 pt-4 dark:border-gray-800">
                                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">Tarif sewa per hari</p>
                                    <p class="mt-1 text-theme-xl font-semibold text-gray-900 dark:text-white">Rp{{ number_format((float) $mobil->harga_sewa_per_hari, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </main>
@endsection
