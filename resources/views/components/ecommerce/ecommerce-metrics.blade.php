<div class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex size-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
            <svg class="size-6 stroke-current text-gray-800 dark:text-white/90" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M3 11h18M5 11l1.5-5h11L19 11M5 11v7a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-7M8 15h8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>

        <div class="mt-5">
            <span class="text-sm text-gray-500 dark:text-gray-400">Total Mobil</span>
            <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">{{ number_format($stats['totalMobil']) }}</h4>
            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Seluruh armada yang tercatat.</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex size-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
            <svg class="size-6 stroke-current text-gray-800 dark:text-white/90" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M3 11h18M5 11l1.5-5h11L19 11M5 11v7a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-7M8 15h8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>

        <div class="mt-5">
            <span class="text-sm text-gray-500 dark:text-gray-400">Mobil Tersedia</span>
            <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">{{ number_format($stats['mobilTersedia']) }}</h4>
            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Armada dengan status tersedia.</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex size-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
            <svg class="size-6 stroke-current text-gray-800 dark:text-white/90" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M7 4v2M17 4v2M4 10h16M5 6h14a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>

        <div class="mt-5">
            <span class="text-sm text-gray-500 dark:text-gray-400">Total Reservasi</span>
            <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">{{ number_format($stats['totalReservasi']) }}</h4>
            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Semua permintaan sewa pelanggan.</p>
        </div>
    </div>
</div>
