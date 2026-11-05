@extends('layouts.landing')

@section('content')
    <header id="beranda" x-data="{ isOpen: false }" class="relative z-30 border-b border-gray-200/80 bg-white/95 backdrop-blur dark:border-gray-800 dark:bg-gray-950/95">
        <div class="mx-auto flex max-w-[1440px] items-center justify-between px-4 py-3 sm:px-6 lg:px-10">
            <a href="#beranda" class="flex items-center gap-2.5" aria-label="Awal Putra Rentcar">
                <img src="{{ asset('images/hero/logo.png') }}" alt="Logo Awal Putra Rentcar" class="size-11 shrink-0 object-contain sm:size-12">
                <span class="text-theme-sm font-bold tracking-tight text-gray-900 dark:text-white sm:text-theme-xl">Awal Putra <span class="text-brand-600 dark:text-brand-400">Rentcar</span></span>
            </a>

            <nav class="hidden items-center gap-8 xl:flex" aria-label="Navigasi utama">
                <a href="#beranda" class="text-theme-sm font-semibold text-gray-900 transition hover:text-brand-600 dark:text-white dark:hover:text-brand-300">Beranda</a>
                <a href="#mobil" class="text-theme-sm font-semibold text-gray-700 transition hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-300">Daftar Mobil</a>
                <a href="#tentang" class="text-theme-sm font-semibold text-gray-700 transition hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-300">Tentang Kami</a>
                <a href="#kontak" class="text-theme-sm font-semibold text-gray-700 transition hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-300">Kontak</a>
            </nav>

            <div class="hidden items-center gap-3 xl:flex">
                @guest
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-2.5 text-theme-sm font-semibold text-gray-800 transition hover:border-gray-400 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500/40 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/[0.05]">Login</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-xl bg-gray-900 px-5 py-2.5 text-theme-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/40 dark:bg-brand-600 dark:hover:bg-brand-500">Daftar</a>
                @else
                    @if (auth()->user()->isPelanggan())
                        <a href="{{ route('pelanggan.rekomendasi.index') }}" class="rounded-xl px-4 py-2.5 text-theme-sm font-semibold text-gray-700 transition hover:bg-brand-50 hover:text-brand-700 dark:text-gray-300 dark:hover:bg-brand-500/15 dark:hover:text-brand-300">Rekomendasi</a>
                    @endif
                    <a href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('account') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-theme-sm font-semibold text-gray-800 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/[0.05]">Akun</a>
                    <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="rounded-xl bg-gray-900 px-4 py-2.5 text-theme-sm font-semibold text-white transition hover:bg-brand-700 dark:bg-brand-600 dark:hover:bg-brand-500">Keluar</button></form>
                @endguest
            </div>

            <button type="button" @click="isOpen = !isOpen" class="inline-flex size-11 items-center justify-center rounded-xl border border-gray-200 text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500/40 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/[0.05] xl:hidden" :aria-expanded="isOpen.toString()" aria-label="Buka navigasi">
                <svg x-show="!isOpen" class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke-width="1.8" stroke-linecap="round" /></svg>
                <svg x-show="isOpen" x-cloak class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" stroke-width="1.8" stroke-linecap="round" /></svg>
            </button>
        </div>

        <div x-show="isOpen" x-cloak x-transition class="border-t border-gray-200 bg-white px-4 py-4 dark:border-gray-800 dark:bg-gray-950 xl:hidden">
            <nav class="mx-auto flex max-w-[1440px] flex-col gap-1" aria-label="Navigasi mobile">
                <a @click="isOpen = false" href="#beranda" class="rounded-xl px-4 py-3 text-theme-sm font-semibold text-gray-800 hover:bg-brand-50 hover:text-brand-700 dark:text-gray-200 dark:hover:bg-brand-500/15 dark:hover:text-brand-300">Beranda</a>
                <a @click="isOpen = false" href="#mobil" class="rounded-xl px-4 py-3 text-theme-sm font-semibold text-gray-800 hover:bg-brand-50 hover:text-brand-700 dark:text-gray-200 dark:hover:bg-brand-500/15 dark:hover:text-brand-300">Daftar Mobil</a>
                <a @click="isOpen = false" href="#tentang" class="rounded-xl px-4 py-3 text-theme-sm font-semibold text-gray-800 hover:bg-brand-50 hover:text-brand-700 dark:text-gray-200 dark:hover:bg-brand-500/15 dark:hover:text-brand-300">Tentang Kami</a>
                <a @click="isOpen = false" href="#kontak" class="rounded-xl px-4 py-3 text-theme-sm font-semibold text-gray-800 hover:bg-brand-50 hover:text-brand-700 dark:text-gray-200 dark:hover:bg-brand-500/15 dark:hover:text-brand-300">Kontak</a>
                <div class="my-2 border-t border-gray-100 dark:border-gray-800"></div>
                @guest
                    <a href="{{ route('login') }}" class="rounded-xl px-4 py-3 text-theme-sm font-semibold text-gray-800 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/[0.05]">Login</a>
                    <a href="{{ route('register') }}" class="rounded-xl bg-gray-900 px-4 py-3 text-center text-theme-sm font-semibold text-white dark:bg-brand-600">Daftar</a>
                @else
                    @if (auth()->user()->isPelanggan())<a href="{{ route('pelanggan.rekomendasi.index') }}" class="rounded-xl px-4 py-3 text-theme-sm font-semibold text-gray-800 hover:bg-brand-50 hover:text-brand-700 dark:text-gray-200 dark:hover:bg-brand-500/15 dark:hover:text-brand-300">Rekomendasi Mobil</a>@endif
                    <a href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('account') }}" class="rounded-xl px-4 py-3 text-theme-sm font-semibold text-gray-800 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/[0.05]">Akun</a>
                    <form action="{{ route('logout') }}" method="POST" class="px-4 py-3">@csrf<button type="submit" class="text-theme-sm font-semibold text-brand-700 dark:text-brand-300">Keluar</button></form>
                @endguest
            </nav>
        </div>
    </header>

    <main
        x-data="{
            search: '',
            category: '',
            transmission: '',
            sort: 'default',
            resultCount: {{ $mobils->count() }},
            init() {
                this.$nextTick(() => this.updateCatalogue());
            },
            matchesCard(card) {
                const search = this.search.trim().toLowerCase();

                return (!search || card.dataset.search.includes(search))
                    && (!this.category || card.dataset.category === this.category)
                    && (!this.transmission || card.dataset.transmission === this.transmission);
            },
            updateCatalogue() {
                this.$nextTick(() => {
                    const cards = Array.from(this.$refs.catalogue?.querySelectorAll('[data-mobil-card]') ?? []);
                    this.resultCount = cards.filter((card) => this.matchesCard(card)).length;
                    this.sortCards(cards);
                });
            },
            sortCards(cards) {
                const sortedCards = [...cards].sort((first, second) => {
                    if (this.sort === 'price-asc') return Number(first.dataset.price) - Number(second.dataset.price);
                    if (this.sort === 'price-desc') return Number(second.dataset.price) - Number(first.dataset.price);
                    return Number(first.dataset.order) - Number(second.dataset.order);
                });

                sortedCards.forEach((card) => this.$refs.catalogue.appendChild(card));
            },
            resetFilters() {
                this.search = '';
                this.category = '';
                this.transmission = '';
                this.sort = 'default';
                this.updateCatalogue();
            },
        }"
    >
        <section
            x-data="{
                activeSlide: 0,
                failedSlides: [],
                autoplay: null,
                isPaused: false,
                isDocumentHidden: document.hidden,
                reducedMotion: false,
                totalSlides: {{ $heroMobils->count() }},
                init() {
                    this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    this.visibilityHandler = () => {
                        this.isDocumentHidden = document.hidden;
                        document.hidden ? this.stopAutoplay() : this.startAutoplay();
                    };
                    document.addEventListener('visibilitychange', this.visibilityHandler);
                    this.startAutoplay();
                },
                destroy() {
                    this.stopAutoplay();
                    document.removeEventListener('visibilitychange', this.visibilityHandler);
                },
                startAutoplay() {
                    if (this.reducedMotion || this.isPaused || this.isDocumentHidden || this.totalSlides < 2 || this.autoplay) return;
                    this.autoplay = window.setInterval(() => this.next(), 4500);
                },
                stopAutoplay() {
                    if (this.autoplay) {
                        window.clearInterval(this.autoplay);
                        this.autoplay = null;
                    }
                },
                pause() { this.isPaused = true; this.stopAutoplay(); },
                resume() { this.isPaused = false; this.startAutoplay(); },
                move(step) {
                    for (let attempt = 1; attempt <= this.totalSlides; attempt++) {
                        const candidate = (this.activeSlide + (step * attempt) + this.totalSlides) % this.totalSlides;
                        if (!this.failedSlides[candidate]) {
                            this.activeSlide = candidate;
                            return;
                        }
                    }
                },
                next() { this.move(1); },
                previous() { this.move(-1); },
                goTo(index) {
                    if (!this.failedSlides[index]) this.activeSlide = index;
                },
                resetAutoplay() {
                    this.stopAutoplay();
                    this.startAutoplay();
                },
            }"
            @focusin="pause()"
            @focusout="if (!$el.contains($event.relatedTarget)) resume()"
            class="relative isolate overflow-hidden bg-gray-950"
            aria-roledescription="carousel"
            aria-label="Pilihan armada Awal Putra Rentcar"
        >
            <div class="absolute inset-0" aria-live="off">
                <img src="{{ asset('images/hero/rentcar-hero.png') }}" alt="" class="size-full object-cover object-center" aria-hidden="true">
                @foreach ($heroMobils as $index => $mobil)
                    <img
                        x-show="activeSlide === {{ $index }} && !failedSlides[{{ $index }}]"
                        x-cloak
                        x-transition:enter="transition duration-700 ease-out motion-reduce:transition-none"
                        x-transition:enter-start="scale-105 opacity-0"
                        x-transition:enter-end="scale-100 opacity-100"
                        x-transition:leave="transition duration-700 ease-out motion-reduce:transition-none"
                        x-transition:leave-start="scale-100 opacity-100"
                        x-transition:leave-end="scale-105 opacity-0"
                        x-on:error="failedSlides[{{ $index }}] = true; if (activeSlide === {{ $index }}) next()"
                        src="{{ $mobil->hero_photo_url }}"
                        alt="{{ $mobil->nama_mobil }}"
                        class="absolute inset-0 size-full object-cover object-center"
                        loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                    >
                @endforeach
                <div class="absolute inset-0 bg-gradient-to-r from-gray-950 via-gray-900/90 to-gray-900/15 dark:from-gray-950 dark:via-gray-950/90"></div>
                <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-gray-950/65 to-transparent"></div>
            </div>

            <div class="relative mx-auto min-h-[610px] max-w-[1440px] px-4 py-16 pb-28 sm:px-6 sm:py-20 sm:pb-28 lg:min-h-[640px] lg:px-10 lg:py-24">
                <div class="flex min-h-[480px] items-center lg:min-h-[520px]">
                    <div class="max-w-2xl landing-hero-enter">
                        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-theme-xs font-semibold text-white backdrop-blur"><span class="size-2 rounded-full bg-brand-400"></span>Armada pilihan untuk perjalanan Anda</div>
                        <h1 class="max-w-xl text-4xl font-bold leading-[1.02] tracking-tight text-white sm:text-6xl lg:text-7xl">Sewa mobil.<br>Jelajahi lebih jauh.<br><span class="text-brand-300">Dengan tenang.</span></h1>
                        <p class="mt-6 max-w-lg text-theme-xl leading-relaxed text-gray-200 sm:text-2xl">Pilih armada yang tepat dengan tarif harian transparan dan proses reservasi yang mudah dari Awal Putra Rentcar.</p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row landing-hero-enter landing-hero-enter-delay">
                            <a href="#mobil" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-6 py-3.5 text-theme-sm font-bold text-white shadow-theme-md transition hover:bg-brand-400 focus:outline-none focus:ring-2 focus:ring-white/80">Cari mobil sekarang <svg class="size-4 stroke-current rtl:rotate-180" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg></a>
                            <a href="{{ auth()->check() && auth()->user()->isPelanggan() ? route('reservasi.create') : route('register') }}" class="inline-flex items-center justify-center rounded-xl border border-white/50 bg-white/10 px-6 py-3.5 text-theme-sm font-bold text-white backdrop-blur transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/80">{{ auth()->check() && auth()->user()->isPelanggan() ? 'Buat Reservasi' : 'Daftar sebagai Penyewa' }}</a>
                        </div>
                        <div class="mt-10 flex flex-wrap gap-x-8 gap-y-4 text-white sm:gap-x-12">
                            <div><p class="text-2xl font-bold sm:text-3xl">{{ $mobils->count() }}<span class="text-brand-300">+</span></p><p class="mt-1 text-theme-xs font-medium text-gray-300">Mobil tersedia</p></div>
                            <div class="border-s border-white/30 ps-8 sm:ps-12"><p class="text-2xl font-bold sm:text-3xl">24/7</p><p class="mt-1 text-theme-xs font-medium text-gray-300">Informasi armada</p></div>
                            <div class="border-s border-white/30 ps-8 sm:ps-12"><p class="text-2xl font-bold sm:text-3xl">100<span class="text-brand-300">%</span></p><p class="mt-1 text-theme-xs font-medium text-gray-300">Tarif terlihat jelas</p></div>
                        </div>
                    </div>
                </div>

                @if ($heroMobils->isNotEmpty())
                    <div class="relative z-10 mt-8 w-full max-w-sm xl:absolute xl:bottom-20 xl:end-24 xl:mt-0">
                        @foreach ($heroMobils as $index => $mobil)
                            <article x-show="activeSlide === {{ $index }} && !failedSlides[{{ $index }}]" x-cloak x-transition:enter="transition duration-500 ease-out motion-reduce:transition-none" x-transition:enter-start="translate-y-3 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition duration-300 ease-in motion-reduce:transition-none" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0" class="rounded-2xl border border-white/20 bg-gray-950/60 p-5 shadow-theme-lg backdrop-blur-md" role="group" aria-roledescription="slide" aria-label="{{ $index + 1 }} dari {{ $heroMobils->count() }}">
                                <p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-300">Armada tersedia</p>
                                <h2 class="mt-2 text-theme-xl font-bold text-white">{{ $mobil->nama_mobil }}</h2>
                                <p class="mt-1 text-theme-sm text-gray-200">{{ $mobil->merek }}{{ $mobil->tipe ? ' · '.$mobil->tipe : '' }}</p>
                                <div class="mt-4 flex items-center justify-between gap-3 border-y border-white/15 py-3 text-theme-sm text-gray-200"><span>{{ $mobil->kategori?->nama_kategori ?? 'Kategori belum tersedia' }}</span><span>{{ $mobil->kapasitas_penumpang }} Penumpang</span></div>
                                <div class="mt-4 flex items-end justify-between gap-4"><div><p class="text-theme-xs text-gray-300">Tarif sewa</p><p class="mt-1 text-theme-xl font-bold text-white">Rp{{ number_format((float) $mobil->harga_sewa_per_hari, 0, ',', '.') }}<span class="text-theme-xs font-medium text-gray-300"> / hari</span></p></div>
                                    @guest
                                        <a href="{{ route('login') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-brand-500 px-4 py-2.5 text-theme-xs font-bold text-white transition hover:bg-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-300">Masuk untuk Sewa</a>
                                    @else
                                        @if (auth()->user()->isPelanggan())
                                            <a href="{{ route('reservasi.create') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-brand-500 px-4 py-2.5 text-theme-xs font-bold text-white transition hover:bg-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-300">Sewa Mobil</a>
                                        @else
                                            <a href="{{ route('dashboard') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-brand-500 px-4 py-2.5 text-theme-xs font-bold text-white transition hover:bg-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-300">Ke Dashboard</a>
                                        @endif
                                    @endguest
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($heroMobils->count() > 1)
                <div class="absolute bottom-7 end-4 z-20 flex items-center gap-3 sm:bottom-10 sm:end-10" aria-label="Kontrol slider armada">
                    <div class="h-1.5 w-14 overflow-hidden rounded-full bg-white/25 sm:w-20" aria-hidden="true">
                        @foreach ($heroMobils as $index => $mobil)
                            <span x-show="activeSlide === {{ $index }}" x-cloak class="hero-slider-progress block h-full w-full rounded-full bg-brand-400" :class="{ 'hero-slider-progress-active': !reducedMotion, 'hero-slider-progress-paused': isPaused || isDocumentHidden }"></span>
                        @endforeach
                    </div>
                    <div class="flex items-center gap-2" role="tablist" aria-label="Pilih gambar armada">
                        @foreach ($heroMobils as $index => $mobil)
                            <button type="button" @click="goTo({{ $index }}); resetAutoplay()" :aria-selected="(activeSlide === {{ $index }}).toString()" aria-label="Tampilkan gambar {{ $index + 1 }} dari {{ $heroMobils->count() }}" class="size-3 rounded-full border border-white/70 transition focus:outline-none focus:ring-2 focus:ring-brand-300 motion-reduce:transition-none" :class="activeSlide === {{ $index }} ? 'bg-brand-400 scale-125' : 'bg-white/35 hover:bg-white/75'" role="tab"></button>
                        @endforeach
                    </div>
                    <div class="flex overflow-hidden rounded-xl border border-white/30 bg-gray-950/30 shadow-theme-sm backdrop-blur">
                        <button type="button" @click="previous(); resetAutoplay()" class="inline-flex size-10 items-center justify-center text-white transition hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-300 motion-reduce:transition-none" aria-label="Gambar sebelumnya"><svg class="size-5 stroke-current rtl:rotate-180" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m15 18-6-6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg></button>
                        <button type="button" @click="next(); resetAutoplay()" class="inline-flex size-10 items-center justify-center border-s border-white/30 text-white transition hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-300 motion-reduce:transition-none" aria-label="Gambar berikutnya"><svg class="size-5 stroke-current rtl:rotate-180" viewBox="0 0 24" fill="none" aria-hidden="true"><path d="m9 18 6-6-6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg></button>
                    </div>
                </div>
            @endif
        </section>

        <section class="relative z-10 mx-auto -mt-7 max-w-[1320px] px-4 sm:-mt-10 sm:px-6 lg:px-8">
            <form @submit.prevent="$nextTick(() => document.getElementById('mobil')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))" class="rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-700 dark:bg-gray-900 sm:p-4">
                <div class="grid gap-2 md:grid-cols-[minmax(0,1.8fr)_minmax(0,1fr)_auto] md:items-center">
                    <label class="flex min-w-0 items-center gap-3 rounded-xl px-3 py-3 sm:px-4"><span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 11h18M5 11l1.5-5h11L19 11M5 11v7a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-7M8 15h8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg></span><span class="min-w-0"><span class="block text-theme-xs font-semibold text-gray-500 dark:text-gray-400">Cari armada</span><input x-model="search" x-on:input.debounce.200ms="$nextTick(() => updateCatalogue())" type="search" placeholder="Nama mobil atau merek" class="mt-0.5 w-full border-0 bg-transparent p-0 text-theme-sm font-semibold text-gray-900 outline-none placeholder:text-gray-400 focus:ring-0 dark:text-white" aria-label="Cari mobil berdasarkan nama atau merek"></span></label>
                    <div class="flex items-center gap-3 border-t border-gray-100 px-3 py-3 md:border-s md:border-t-0 dark:border-gray-800 sm:px-4"><span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-700 dark:bg-white/[0.06] dark:text-gray-300"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 8v4l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg></span><span><span class="block text-theme-xs font-semibold text-gray-500 dark:text-gray-400">Layanan</span><span class="text-theme-sm font-semibold text-gray-900 dark:text-white">Sewa harian</span></span></div>
                    <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-gray-900 px-6 py-3 text-theme-sm font-bold text-white transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/40 dark:bg-brand-600 dark:hover:bg-brand-500">Cari Mobil <svg class="size-4 stroke-current rtl:rotate-180" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg></button>
                </div>
                <p class="px-3 pt-2 text-theme-xs text-gray-500 dark:text-gray-400 sm:px-4">Pencarian menyaring armada tersedia yang ditampilkan di halaman ini.</p>
            </form>
        </section>

        <section
            x-data="{
                visible: false,
                init() {
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        this.visible = true;
                        return;
                    }
                    const observer = new IntersectionObserver(([entry]) => {
                        if (entry.isIntersecting) {
                            this.visible = true;
                            observer.disconnect();
                        }
                    }, { threshold: 0.2 });
                    observer.observe(this.$el);
                },
            }"
            class="mx-auto max-w-[1440px] px-4 py-14 sm:px-6 sm:py-16 lg:px-10"
        >
            <div :class="visible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'" class="grid gap-5 transition duration-700 ease-out motion-reduce:transition-none sm:grid-cols-2 lg:grid-cols-4">
                <article class="flex gap-4 rounded-2xl p-2"><span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300"><svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 11h18M5 11l1.5-5h11L19 11M5 11v7a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-7M8 15h8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg></span><div><h2 class="text-theme-sm font-bold text-gray-900 dark:text-white">Armada tersedia</h2><p class="mt-1 text-theme-xs leading-relaxed text-gray-600 dark:text-gray-400">Mobil yang tampil diperbarui dari data armada.</p></div></article>
                <article class="flex gap-4 rounded-2xl p-2"><span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300"><svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3v18M3 12h18" stroke-width="1.8" stroke-linecap="round" /></svg></span><div><h2 class="text-theme-sm font-bold text-gray-900 dark:text-white">Tarif transparan</h2><p class="mt-1 text-theme-xs leading-relaxed text-gray-600 dark:text-gray-400">Harga sewa per hari terlihat sebelum reservasi.</p></div></article>
                <article class="flex gap-4 rounded-2xl p-2"><span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300"><svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 8v4l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg></span><div><h2 class="text-theme-sm font-bold text-gray-900 dark:text-white">Reservasi mudah</h2><p class="mt-1 text-theme-xs leading-relaxed text-gray-600 dark:text-gray-400">Pilih mobil dan ajukan penyewaan dari akun Anda.</p></div></article>
                <article class="flex gap-4 rounded-2xl p-2"><span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300"><svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 4v16M4 12h16" stroke-width="1.8" stroke-linecap="round" /></svg></span><div><h2 class="text-theme-sm font-bold text-gray-900 dark:text-white">Informasi jelas</h2><p class="mt-1 text-theme-xs leading-relaxed text-gray-600 dark:text-gray-400">Kategori dan status mobil mudah dipantau.</p></div></article>
            </div>
        </section>

        <section id="mobil" class="scroll-mt-6 border-y border-gray-100 bg-gray-50 py-16 dark:border-gray-800 dark:bg-white/[0.02] sm:py-20">
            <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-theme-sm font-bold text-brand-700 dark:text-brand-300">Pilihan armada</p>
                        <h2 class="mt-2 text-title-sm font-bold tracking-tight text-gray-900 dark:text-white sm:text-title-md">Mobil tersedia untuk perjalanan Anda</h2>
                        <p class="mt-3 text-theme-sm leading-relaxed text-gray-600 dark:text-gray-300">Pilih mobil sesuai kebutuhan Anda. Harga di bawah merupakan tarif sewa harian.</p>
                    </div>
                    @auth
                        @if (auth()->user()->isPelanggan())
                            <a href="{{ route('reservasi.create') }}" class="inline-flex shrink-0 items-center gap-2 text-theme-sm font-bold text-brand-700 transition hover:text-brand-600 dark:text-brand-300 dark:hover:text-brand-200">Buat reservasi <svg class="size-4 stroke-current rtl:rotate-180" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg></a>
                        @endif
                    @endauth
                </div>

                @if ($mobils->isNotEmpty())
                    <div class="mt-8 rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:p-5">
                        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4 xl:items-end">
                            @if ($availableCategories->isNotEmpty())
                                <label class="block">
                                    <span class="mb-2 block text-theme-xs font-semibold text-gray-600 dark:text-gray-300">Kategori</span>
                                    <select x-model="category" x-on:change="$nextTick(() => updateCatalogue())" class="h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-theme-sm font-medium text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200">
                                        <option value="">Semua kategori</option>
                                        @foreach ($availableCategories as $category)<option value="{{ $category->id }}">{{ $category->nama_kategori }}</option>@endforeach
                                    </select>
                                </label>
                            @endif
                            @if ($availableTransmissions->isNotEmpty())
                                <label class="block">
                                    <span class="mb-2 block text-theme-xs font-semibold text-gray-600 dark:text-gray-300">Transmisi</span>
                                    <select x-model="transmission" x-on:change="$nextTick(() => updateCatalogue())" class="h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-theme-sm font-medium text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200">
                                        <option value="">Semua transmisi</option>
                                        @foreach ($availableTransmissions as $transmisi)<option value="{{ strtolower($transmisi) }}">{{ $transmisi }}</option>@endforeach
                                    </select>
                                </label>
                            @endif
                            <label class="block">
                                <span class="mb-2 block text-theme-xs font-semibold text-gray-600 dark:text-gray-300">Urutkan harga</span>
                                <select x-model="sort" x-on:change="$nextTick(() => updateCatalogue())" class="h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-theme-sm font-medium text-gray-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200">
                                    <option value="default">Terbaru</option>
                                    <option value="price-asc">Harga terendah</option>
                                    <option value="price-desc">Harga tertinggi</option>
                                </select>
                            </label>
                            <button type="button" x-show="search || category || transmission || sort !== 'default'" x-cloak x-on:click="resetFilters()" class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-300 px-4 text-theme-sm font-semibold text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-brand-500/15 dark:hover:text-brand-300">Atur ulang filter</button>
                        </div>
                        <p class="mt-4 text-theme-sm text-gray-600 dark:text-gray-300"><span x-text="resultCount"></span> mobil tersedia ditemukan</p>
                    </div>
                @endif

                <div x-ref="catalogue" class="mt-9 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse ($mobils as $index => $mobil)
                        <article x-show="matchesCard($el)" x-transition:enter="transition duration-300 ease-out motion-reduce:transition-none" x-transition:enter-start="translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition duration-200 ease-in motion-reduce:transition-none" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0" data-mobil-card data-order="{{ $index }}" data-price="{{ $mobil->harga_sewa_per_hari }}" data-category="{{ $mobil->kategori_mobil_id }}" data-transmission="{{ strtolower($mobil->transmisi ?? '') }}" data-search="{{ strtolower($mobil->nama_mobil.' '.$mobil->merek.' '.($mobil->kategori?->nama_kategori ?? '')) }}" class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs transition duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-theme-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-brand-500/30">
                            <div class="relative aspect-[16/10] overflow-hidden bg-gray-100 dark:bg-gray-800">
                                @if ($mobil->foto)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($mobil->foto) }}" alt="{{ $mobil->nama_mobil }}" class="size-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">@else<div class="flex size-full items-center justify-center text-gray-400 dark:text-gray-500"><svg class="size-12 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 11h18M5 11l1.5-5h11L19 11M5 11v7a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-7M8 15h8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg></div>@endif
                                <span class="absolute end-4 top-4 rounded-full bg-white/95 px-3 py-1.5 text-theme-xs font-bold text-brand-700 shadow-theme-xs backdrop-blur dark:bg-gray-950/90 dark:text-brand-300">Tersedia</span>
                            </div>
                            <div class="p-5 sm:p-6"><p class="text-theme-xs font-semibold text-brand-700 dark:text-brand-300">{{ $mobil->kategori?->nama_kategori ?? 'Armada rental' }}</p><h3 class="mt-1 text-theme-xl font-bold text-gray-900 dark:text-white">{{ $mobil->nama_mobil }}</h3><p class="mt-2 text-theme-sm text-gray-500 dark:text-gray-400">{{ $mobil->merek }}{{ $mobil->tipe ? ' · '.$mobil->tipe : '' }}{{ $mobil->kapasitas_penumpang ? ' · '.$mobil->kapasitas_penumpang.' penumpang' : '' }}</p>@if ($mobil->deskripsi)<p class="mt-4 line-clamp-2 text-theme-sm leading-relaxed text-gray-600 dark:text-gray-300">{{ $mobil->deskripsi }}</p>@endif<div class="mt-5 flex items-end justify-between gap-4 border-t border-gray-100 pt-4 dark:border-gray-800"><div><p class="text-theme-xs text-gray-500 dark:text-gray-400">Mulai dari</p><p class="mt-1 text-theme-xl font-bold text-gray-900 dark:text-white">Rp{{ number_format((float) $mobil->harga_sewa_per_hari, 0, ',', '.') }}<span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400"> / hari</span></p></div>@auth @if (auth()->user()->isPelanggan())<a href="{{ route('reservasi.create') }}" class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-gray-900 text-white transition hover:bg-brand-700 dark:bg-brand-600 dark:hover:bg-brand-500" aria-label="Reservasi {{ $mobil->nama_mobil }}"><svg class="size-4 stroke-current rtl:rotate-180" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg></a>@endif @endauth</div></div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center dark:border-gray-700 dark:bg-white/[0.03]"><svg class="mx-auto size-10 stroke-current text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 11h18M5 11l1.5-5h11L19 11M5 11v7a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-7M8 15h8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg><h3 class="mt-4 text-theme-xl font-bold text-gray-800 dark:text-white">Belum ada mobil tersedia</h3><p class="mx-auto mt-2 max-w-md text-theme-sm text-gray-500 dark:text-gray-400">Data armada dengan status tersedia belum ada untuk ditampilkan.</p></div>
                    @endforelse
                </div>
                @if ($mobils->isNotEmpty())
                    <div x-show="resultCount === 0" x-cloak class="mt-8 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-8 text-center dark:border-gray-700 dark:bg-gray-900">
                        <h3 class="text-theme-xl font-bold text-gray-900 dark:text-white">Mobil tidak ditemukan</h3>
                        <p class="mt-2 text-theme-sm text-gray-600 dark:text-gray-300">Coba ubah kata kunci, kategori, atau transmisi yang dipilih.</p>
                        <button type="button" x-on:click="resetFilters()" class="mt-4 text-theme-sm font-bold text-brand-700 hover:text-brand-600 dark:text-brand-300 dark:hover:text-brand-200">Tampilkan semua mobil</button>
                    </div>
                @endif
            </div>
        </section>

        @auth
            @if (auth()->user()->isPelanggan())
                <section class="bg-white py-16 dark:bg-gray-950 sm:py-20"><div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10"><div class="relative overflow-hidden rounded-3xl bg-gray-900 px-6 py-10 shadow-theme-lg sm:px-10 sm:py-12"><div class="absolute -end-16 -top-16 size-64 rounded-full bg-brand-500/25 blur-3xl"></div><div class="relative max-w-2xl"><p class="text-theme-sm font-bold text-brand-300">Untuk Anda</p><h2 class="mt-2 text-title-sm font-bold tracking-tight text-white sm:text-title-md">Temukan mobil sesuai preferensi perjalanan Anda.</h2><p class="mt-4 text-theme-sm leading-relaxed text-gray-300">Lihat rekomendasi yang dihitung dari rating penyewa dengan metode User-Based Collaborative Filtering.</p><a href="{{ route('pelanggan.rekomendasi.index') }}" class="mt-7 inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-theme-sm font-bold text-white transition hover:bg-brand-400">Lihat Rekomendasi Mobil <svg class="size-4 stroke-current rtl:rotate-180" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg></a></div></div></div></section>
            @endif
        @endauth

        <section id="tentang" class="scroll-mt-6 border-t border-gray-100 bg-gray-50 py-16 dark:border-gray-800 dark:bg-white/[0.02] sm:py-20"><div class="mx-auto grid max-w-[1440px] gap-8 px-4 sm:px-6 lg:grid-cols-[.8fr_1.2fr] lg:px-10"><div><p class="text-theme-sm font-bold text-brand-700 dark:text-brand-300">Tentang Awal Putra Rentcar</p><h2 class="mt-2 text-title-sm font-bold tracking-tight text-gray-900 dark:text-white">Perjalanan yang dimulai dengan pilihan yang jelas.</h2></div><p class="max-w-3xl text-theme-xl leading-relaxed text-gray-600 dark:text-gray-300">Kami menghadirkan informasi armada, kategori, status ketersediaan, dan tarif sewa dalam satu tempat agar Anda dapat memilih mobil dengan lebih percaya diri.</p></div></section>
        <section id="kontak" class="scroll-mt-6 bg-gray-950 py-12 text-white"><div class="mx-auto flex max-w-[1440px] flex-col gap-6 px-4 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-10"><div><p class="text-theme-sm font-bold text-brand-300">Awal Putra Rentcar</p><h2 class="mt-2 text-theme-xl font-bold">Siap untuk perjalanan berikutnya?</h2></div><a href="#mobil" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-theme-sm font-bold text-white transition hover:bg-brand-400">Pilih Mobil <svg class="size-4 stroke-current rtl:rotate-180" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg></a></div></section>
    </main>

    <footer class="bg-gray-950 pb-7 text-gray-400"><div class="mx-auto flex max-w-[1440px] flex-col gap-2 border-t border-white/10 px-4 pt-7 text-theme-xs sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-10"><p>© {{ now()->year }} Awal Putra Rentcar.</p><p>Informasi armada rental mobil.</p></div></footer>
@endsection
