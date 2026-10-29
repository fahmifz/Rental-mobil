<div class="rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="rounded-2xl bg-white px-5 pb-11 pt-5 dark:bg-gray-900 sm:px-6 sm:pt-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Ketersediaan Armada</h3>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Persentase mobil dengan status tersedia.</p>
        </div>

        <div class="relative max-h-[195px]">
            <div id="chartTwo" class="h-full" data-available-percent="{{ $stats['totalMobil'] > 0 ? round(($stats['mobilTersedia'] / $stats['totalMobil']) * 100) : 0 }}"></div>
        </div>

        <p class="mx-auto mt-1.5 w-full max-w-[380px] text-center text-sm text-gray-500 dark:text-gray-400 sm:text-base">
            {{ $stats['mobilTersedia'] }} dari {{ $stats['totalMobil'] }} mobil tersedia untuk reservasi.
        </p>
    </div>

    <div class="flex flex-wrap items-center justify-center gap-5 px-6 py-3.5 sm:gap-8 sm:py-5">
        <div>
            <p class="mb-1 text-center text-theme-xs text-gray-500 dark:text-gray-400 sm:text-sm">Menunggu Persetujuan</p>
            <p class="text-center text-base font-semibold text-gray-800 dark:text-white/90 sm:text-lg">{{ number_format($stats['menungguKonfirmasi']) }}</p>
        </div>
        <div class="h-7 w-px bg-gray-200 dark:bg-gray-800"></div>
        <div>
            <p class="mb-1 text-center text-theme-xs text-gray-500 dark:text-gray-400 sm:text-sm">Disetujui</p>
            <p class="text-center text-base font-semibold text-gray-800 dark:text-white/90 sm:text-lg">{{ number_format($stats['disetujui']) }}</p>
        </div>
        <div class="h-7 w-px bg-gray-200 dark:bg-gray-800"></div>
        <div>
            <p class="mb-1 text-center text-theme-xs text-gray-500 dark:text-gray-400 sm:text-sm">Ditolak</p>
            <p class="text-center text-base font-semibold text-gray-800 dark:text-white/90 sm:text-lg">{{ number_format($stats['ditolak']) }}</p>
        </div>
    </div>
</div>
