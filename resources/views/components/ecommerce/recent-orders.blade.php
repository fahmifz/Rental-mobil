<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Reservasi Terbaru</h3>
        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Lima reservasi terbaru yang dibuat pelanggan.</p>
    </div>

    <div class="max-w-full overflow-x-auto custom-scrollbar">
        <table class="min-w-full">
            <thead>
                <tr class="border-t border-gray-100 dark:border-gray-800">
                    <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Mobil</p></th>
                    <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Pelanggan</p></th>
                    <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Periode Sewa</p></th>
                    <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Total Biaya</p></th>
                    <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p></th>
                </tr>
            </thead>
            <tbody>
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
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td class="py-3 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $reservasi->mobil->nama_mobil }}</td>
                        <td class="py-3 text-theme-sm text-gray-600 dark:text-gray-300">
                            <p>{{ $reservasi->user->name }}</p>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $reservasi->user->email }}</p>
                        </td>
                        <td class="py-3 text-theme-sm text-gray-600 dark:text-gray-300">{{ $reservasi->tanggal_mulai->format('d M Y') }} â€“ {{ $reservasi->tanggal_selesai->format('d M Y') }}</td>
                        <td class="py-3 text-theme-sm text-gray-600 dark:text-gray-300">Rp{{ number_format((float) $reservasi->total_biaya, 0, ',', '.') }}</td>
                        <td class="py-3"><x-ui.badge :color="$statusColor">{{ ucwords(str_replace('_', ' ', $reservasi->status)) }}</x-ui.badge></td>
                    </tr>
                @empty
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td colspan="5" class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada data reservasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
