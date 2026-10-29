<div class="rounded-2xl border border-gray-200 bg-white px-5 pb-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Reservasi Enam Bulan Terakhir</h3>
        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Jumlah reservasi berdasarkan waktu pembuatan reservasi.</p>
    </div>

    <div class="max-w-full overflow-x-auto custom-scrollbar">
        <div id="chartThree" class="-ms-4 min-w-[700px] ps-2 xl:min-w-full" data-categories='@json($monthlyReservationChart['categories'])' data-reservations='@json($monthlyReservationChart['data'])'></div>
    </div>
</div>
