@extends('layouts.landing')

@section('content')
    <header id="beranda" x-data="{ isOpen: false }" class="relative z-20 border-b border-gray-200 bg-white/95 dark:border-gray-800 dark:bg-gray-900/95">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            
        <a href="#beranda" class="flex items-center gap-3" aria-label="Awal Putra Rentcar">
            <img
                src="{{ asset('images/hero/logo.png') }}"
                alt="Logo Awal Putra Rentcar"
                class="h-14 w-14 object-contain shrink-0"
            >
            <span class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Awal Putra Rentcar
            </span>
        </a>


            <nav class="hidden items-center gap-7 lg:flex" aria-label="Navigasi utama">
                <a href="#beranda" class="text-theme-sm font-medium text-gray-600 transition hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-300">Beranda</a>
                <a href="#mobil" class="text-theme-sm font-medium text-gray-600 transition hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-300">Daftar Mobil</a>
                <a href="#tentang" class="text-theme-sm font-medium text-gray-600 transition hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-300">Tentang Kami</a>
                <a href="#kontak" class="text-theme-sm font-medium text-gray-600 transition hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-300">Kontak</a>
            </nav>

            <div class="hidden items-center gap-3 lg:flex">
                @guest
                    <a href="{{ route('login') }}" class="rounded-lg px-4 py-2 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">Login</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-brand-500 px-4 py-2 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Daftar</a>
                @else
                    <a href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('account') }}" class="rounded-lg px-4 py-2 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">Akun</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Keluar</button>
                    </form>
                @endguest
            </div>

            <button type="button" @click="isOpen = !isOpen" class="inline-flex size-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-300 lg:hidden" :aria-expanded="isOpen.toString()" aria-label="Buka navigasi">
                <svg x-show="!isOpen" class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke-width="1.5" stroke-linecap="round" /></svg>
                <svg x-show="isOpen" x-cloak class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" stroke-width="1.5" stroke-linecap="round" /></svg>
            </button>
        </div>

        <div x-show="isOpen" x-cloak class="border-t border-gray-200 px-4 py-4 dark:border-gray-800 lg:hidden">
            <nav class="mx-auto flex max-w-7xl flex-col gap-3" aria-label="Navigasi mobile">
                <a @click="isOpen = false" href="#beranda" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">Beranda</a>
                <a @click="isOpen = false" href="#mobil" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">Daftar Mobil</a>
                <a @click="isOpen = false" href="#tentang" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">Tentang Kami</a>
                <a @click="isOpen = false" href="#kontak" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">Kontak</a>
                @guest
                    <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">Login</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-brand-500 px-3 py-2 text-theme-sm font-medium text-white">Daftar</a>
                @else
                    <a href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('account') }}" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">Akun</a>
                    <form action="{{ route('logout') }}" method="POST" class="px-3 py-2">
                        @csrf
                        <button type="submit" class="text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">Keluar</button>
                    </form>
                @endguest
            </nav>
        </div>
    </header>

    <main>
        <section class="border-b border-gray-100 bg-gray-50 py-14 dark:border-gray-800 dark:bg-gray-900 sm:py-20">
            <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                <div class="max-w-xl">
                    <p class="mb-4 text-theme-sm font-semibold text-brand-600 dark:text-brand-400">Awal Putra Rentcar</p>
                    <h1 class="text-title-sm font-semibold tracking-tight text-gray-900 dark:text-white sm:text-title-md">Temukan Mobil yang Tepat untuk Perjalanan Anda</h1>
                    <p class="mt-5 max-w-lg text-theme-xl text-gray-600 dark:text-gray-300">Lihat pilihan armada rental dan informasi tarif harian dalam satu tempat yang mudah diakses.</p>
                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <a href="#mobil" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Lihat Mobil</a>
                        <a href="{{ auth()->check() ? (auth()->user()->isAdmin() ? route('dashboard') : route('account')) : route('register') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">Mulai Sewa</a>
                    </div>
                    @guest
                        <p class="mt-3 text-theme-xs text-gray-500 dark:text-gray-400">Buat akun penyewa untuk memulai proses sewa.</p>
                    @endguest
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-800">
                    <img src="/images/hero/rentcar-hero.png" alt="Mobil rental Awal Putra Rentcar" class="h-full w-full object-cover" loading="eager">
                </div>
            </div>
        </section>

        <section id="mobil" class="scroll-mt-8 py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-theme-sm font-semibold text-brand-600 dark:text-brand-400">Armada</p>
                    <h2 class="mt-2 text-title-sm font-semibold text-gray-900 dark:text-white">Mobil yang Tersedia</h2>
                    <p class="mt-3 text-theme-sm text-gray-600 dark:text-gray-300">Daftar berikut diambil dari data armada dengan status tersedia.</p>
                </div>

                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($mobils as $mobil)
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
                                        <h3 class="text-theme-xl font-semibold text-gray-900 dark:text-white">{{ $mobil->nama_mobil }}</h3>
                                        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $mobil->kategori?->nama_kategori ?? 'Tanpa kategori' }}</p>
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
                    @empty
                        <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-14 text-center dark:border-gray-700 dark:bg-white/[0.03]">
                            <svg class="mx-auto size-10 stroke-current text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 11h18M5 11l1.5-5h11L19 11M5 11v7a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-7M8 15h8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            <h3 class="mt-4 text-theme-xl font-semibold text-gray-800 dark:text-white/90">Belum ada mobil tersedia</h3>
                            <p class="mx-auto mt-2 max-w-md text-theme-sm text-gray-500 dark:text-gray-400">Data armada dengan status tersedia belum ada untuk ditampilkan.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="tentang" class="scroll-mt-8 bg-gray-50 py-16 dark:bg-white/[0.02] sm:py-20">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                <div>
                    <p class="text-theme-sm font-semibold text-brand-600 dark:text-brand-400">Tentang Kami</p>
                    <h2 class="mt-2 text-title-sm font-semibold text-gray-900 dark:text-white">Informasi armada yang lebih mudah diakses</h2>
                </div>
                <p class="text-theme-xl leading-relaxed text-gray-600 dark:text-gray-300">Awal Putra Rentcar menyediakan halaman untuk melihat armada yang tercatat, kategorinya, status ketersediaan, dan tarif sewa harian. Informasi layanan akan terus dilengkapi seiring pengembangan aplikasi.</p>
            </div>
        </section>

        <section class="py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-theme-sm font-semibold text-brand-600 dark:text-brand-400">Keunggulan</p>
                    <h2 class="mt-2 text-title-sm font-semibold text-gray-900 dark:text-white">Dirancang untuk informasi yang jelas</h2>
                </div>
                <div class="mt-8 grid gap-5 md:grid-cols-3">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 11h18M5 11l1.5-5h11L19 11M5 11v7a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-7M8 15h8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg></div>
                        <h3 class="mt-5 text-theme-xl font-semibold text-gray-900 dark:text-white">Armada tercatat</h3>
                        <p class="mt-2 text-theme-sm text-gray-600 dark:text-gray-300">Nama, merek, kategori, dan tarif armada ditampilkan dari data aplikasi.</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3v18M3 12h18" stroke-width="1.5" stroke-linecap="round" /></svg></div>
                        <h3 class="mt-5 text-theme-xl font-semibold text-gray-900 dark:text-white">Kategori yang terstruktur</h3>
                        <p class="mt-2 text-theme-sm text-gray-600 dark:text-gray-300">Kategori mobil membantu pengelolaan dan pembacaan informasi armada.</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 8v4l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg></div>
                        <h3 class="mt-5 text-theme-xl font-semibold text-gray-900 dark:text-white">Status ketersediaan</h3>
                        <p class="mt-2 text-theme-sm text-gray-600 dark:text-gray-300">Mobil yang ditampilkan pada halaman publik difilter berdasarkan status tersedia.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="kontak" class="scroll-mt-8 border-t border-gray-200 bg-gray-50 py-14 dark:border-gray-800 dark:bg-white/[0.02]">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-title-sm font-semibold text-gray-900 dark:text-white">Kontak</h2>
                <p class="mt-3 max-w-2xl text-theme-sm text-gray-600 dark:text-gray-300">Informasi kontak Awal Putra Rentcar akan dilengkapi di halaman ini.</p>
            </div>
        </section>
    </main>

    <footer class="bg-white py-7 dark:bg-gray-900">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 text-theme-sm text-gray-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8 dark:text-gray-400">
            <p>© {{ now()->year }} Awal Putra Rentcar.</p>
            <p>Informasi armada rental mobil.</p>
        </div>
    </footer>
@endsection
