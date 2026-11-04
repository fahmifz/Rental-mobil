@extends('layouts.landing')

@section('content')
    <main class="min-h-screen bg-gray-50 px-4 py-6 dark:bg-gray-900 sm:px-6 sm:py-10">
        <section class="mx-auto w-full max-w-6xl">
            @if (session('success'))
                <div class="mb-5 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-theme-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400"
                    role="status">{{ session('success') }}</div>
            @endif

            <section class="relative mt-5 overflow-hidden rounded-2xl bg-gray-900 shadow-theme-lg dark:bg-gray-800"
                aria-labelledby="dashboard-heading">

                <!-- Dekorasi background -->
                <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-teal-500/10 blur-3xl"
                    aria-hidden="true"></div>

                <div class="pointer-events-none absolute -bottom-20 left-1/3 h-48 w-48 rounded-full bg-sky-500/10 blur-3xl"
                    aria-hidden="true"></div>

                <!-- Konten hero -->
                <div class="relative z-10 p-6 sm:p-8 lg:p-10">

                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                        <!-- Judul dan deskripsi -->
                        <div class="min-w-0 flex-1">

                            <p class="text-sm font-semibold uppercase tracking-wider text-white">
                                Dashboard Penyewa
                            </p>

                            <h1 id="dashboard-heading"
                                class="mt-3 break-words text-2xl font-bold leading-tight text-white sm:text-3xl">
                                Selamat datang,
                                <span class="text-teal-300">{{ $user->name }}</span>
                            </h1>

                            <p class="mt-4 max-w-xl text-sm leading-relaxed text-gray-300 sm:text-base">
                                Atur perjalanan Anda, pantau reservasi, dan temukan mobil
                                yang sesuai dengan preferensi Anda.
                            </p>

                        </div>

                        <!-- Tombol aksi -->
                        <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center lg:shrink-0">

                            <a href="{{ route('reservasi.create') }}"
                                class="inline-flex h-11 w-full items-center justify-center whitespace-nowrap rounded-lg bg-teal-600 px-5 text-sm font-semibold text-white transition hover:bg-teal-500 sm:w-auto">
                                Buat Reservasi
                            </a>

                            <a href="{{ route('pelanggan.rekomendasi.index') }}"
                                class="inline-flex h-11 w-full items-center justify-center whitespace-nowrap rounded-lg border border-white/30 bg-white/10 px-5 text-sm font-semibold text-white transition hover:bg-white/20 sm:w-auto">
                                Lihat Rekomendasi
                            </a>

                        </div>

                    </div>

                </div>
            </section>


            <section class="mt-6 grid gap-4 sm:grid-cols-3" aria-label="Ringkasan aktivitas">
                <article
                    class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Total Reservasi</p>
                    <p class="mt-2 text-title-md font-semibold text-gray-900 dark:text-white">
                        {{ $statistics['total_reservasi'] }}</p>
                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Seluruh riwayat sewa Anda</p>
                </article>
                <article
                    class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Penyewaan Aktif</p>
                    <p class="mt-2 text-title-md font-semibold text-brand-700 dark:text-brand-300">
                        {{ $statistics['penyewaan_aktif'] }}</p>
                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Reservasi disetujui yang berlangsung hari
                        ini</p>
                </article>
                <article
                    class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Rating Diberikan</p>
                    <p class="mt-2 text-title-md font-semibold text-gray-900 dark:text-white">
                        {{ $statistics['rating_diberikan'] }}</p>
                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Kontribusi Anda untuk rekomendasi</p>
                </article>
            </section>

            <section class="mt-6" aria-labelledby="akses-cepat-heading">
                <div class="mb-3">
                    <p class="text-theme-sm font-semibold text-brand-600 dark:text-brand-400">Akses Cepat</p>
                    <h2 id="akses-cepat-heading" class="mt-1 text-title-sm font-semibold text-gray-900 dark:text-white">
                        Mulai perjalanan Anda</h2>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <a href="{{ route('home') }}#mobil"
                        class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-theme-sm focus:outline-none focus:ring-3 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-500/30">
                        <span
                            class="inline-flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300"><svg
                                class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M3 11h18M5 11l1.5-5h11L19 11M5 11v7a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-7M8 15h8"
                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg></span>
                        <h3 class="mt-4 text-theme-xl font-semibold text-gray-900 dark:text-white">Jelajahi Mobil</h3>
                        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Lihat armada yang tersedia untuk
                            perjalanan berikutnya.</p>
                    </a>
                    <a href="{{ route('pelanggan.rekomendasi.index') }}"
                        class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-theme-sm focus:outline-none focus:ring-3 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-500/30">
                        <span
                            class="inline-flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300"><svg
                                class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 20V10m0 0L8 14m4-4 4 4M5 5h14" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg></span>
                        <h3 class="mt-4 text-theme-xl font-semibold text-gray-900 dark:text-white">Rekomendasi Mobil</h3>
                        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Temukan pilihan berdasarkan pola
                            rating pelanggan.</p>
                    </a>
                    <a href="{{ route('reservasi.index') }}"
                        class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-theme-sm focus:outline-none focus:ring-3 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-500/30">
                        <span
                            class="inline-flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300"><svg
                                class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path
                                    d="M7 3v3m10-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"
                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg></span>
                        <h3 class="mt-4 text-theme-xl font-semibold text-gray-900 dark:text-white">Reservasi Saya</h3>
                        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Pantau status sewa dan beri rating
                            setelah selesai.</p>
                    </a>
                </div>
            </section>

            <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                <section
                    class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"
                    aria-labelledby="reservasi-terbaru-heading">
                    <div
                        class="flex items-center justify-between gap-4 border-b border-gray-100 px-5 py-5 dark:border-gray-800 sm:px-6">
                        <div>
                            <h2 id="reservasi-terbaru-heading"
                                class="text-theme-xl font-semibold text-gray-900 dark:text-white">Reservasi Terbaru</h2>
                            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Tiga aktivitas sewa terbaru
                                Anda.</p>
                        </div><a href="{{ route('reservasi.index') }}"
                            class="text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">Lihat
                            semua</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($recentReservations as $reservasi)
                            @php
                                $statusColor = match ($reservasi->status) {
                                    'menunggu_konfirmasi' => 'warning',
                                    'disetujui' => 'success',
                                    'ditolak' => 'error',
                                    'dibatalkan' => 'light',
                                    'selesai' => 'primary',
                                };
                            @endphp
                            <a href="{{ route('reservasi.show', $reservasi) }}"
                                class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 transition hover:bg-gray-50 dark:hover:bg-white/[0.03] sm:px-6">
                                <div class="min-w-0">
                                    <p class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                                        {{ $reservasi->mobil->nama_mobil }}</p>
                                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">
                                        {{ $reservasi->tanggal_mulai->format('d M Y') }} –
                                        {{ $reservasi->tanggal_selesai->format('d M Y') }}</p>
                                </div><x-ui.badge
                                    :color="$statusColor">{{ ucwords(str_replace('_', ' ', $reservasi->status)) }}</x-ui.badge>
                            </a>
                        @empty
                            <div class="px-5 py-10 text-center sm:px-6">
                                <p class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">Belum ada reservasi
                                </p>
                                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Jelajahi mobil untuk memulai
                                    perjalanan pertama Anda.</p><a href="{{ route('reservasi.create') }}"
                                    class="mt-4 inline-flex text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">Buat
                                    Reservasi</a>
                            </div>
                        @endforelse
                    </div>
                </section>

                <aside
                    class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6"
                    aria-labelledby="profil-ringkas-heading">
                    <div class="flex items-center gap-3"><span
                            class="inline-flex size-12 items-center justify-center rounded-full bg-brand-50 text-theme-xl font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                        <div class="min-w-0">
                            <h2 id="profil-ringkas-heading"
                                class="truncate text-theme-xl font-semibold text-gray-900 dark:text-white">
                                {{ $user->name }}</h2>
                            <p class="truncate text-theme-sm text-gray-500 dark:text-gray-400">Pelanggan</p>
                        </div>
                    </div>
                    <dl class="mt-5 space-y-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <div>
                            <dt class="text-theme-xs text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="mt-1 break-all text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-theme-xs text-gray-500 dark:text-gray-400">Bergabung sejak</dt>
                            <dd class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $user->created_at?->format('d M Y') ?? '-' }}</dd>
                        </div>
                    </dl>
                    <form action="{{ route('logout') }}" method="POST"
                        class="mt-6 border-t border-gray-100 pt-5 dark:border-gray-800">@csrf<button type="submit"
                            class="inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-error-200 bg-white px-4 py-2.5 text-theme-sm font-medium text-error-600 transition hover:bg-error-50 focus:outline-none focus:ring-3 focus:ring-error-500/15 dark:border-error-500/20 dark:bg-white/[0.03] dark:text-error-400 dark:hover:bg-error-500/10">Keluar</button>
                    </form>
                </aside>
            </div>
        </section>
    </main>
@endsection
