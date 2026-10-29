@extends('layouts.landing')

@section('content')
    <main class="min-h-screen bg-gray-50 px-4 py-10 dark:bg-gray-900 sm:px-6">
        <section class="mx-auto w-full max-w-5xl rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-theme-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400" role="status">{{ session('success') }}</div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <a href="{{ route('account') }}" class="text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">Kembali ke akun</a>
                    <h1 class="mt-3 text-title-sm font-semibold text-gray-900 dark:text-white">Reservasi Saya</h1>
                </div>
                <a href="{{ route('reservasi.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Buat Reservasi</a>
            </div>

            @if ($pendingRatingsCount > 0)
                <div class="mt-6 rounded-xl border border-warning-200 bg-warning-50 px-4 py-3 text-theme-sm text-warning-700 dark:border-warning-500/20 dark:bg-warning-500/15 dark:text-warning-400">
                    Ada {{ $pendingRatingsCount }} reservasi selesai yang menunggu rating.
                </div>
            @else
                <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-theme-sm text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                    Tidak ada reservasi selesai yang menunggu rating.
                </div>
            @endif

            @if ($reservasis->isEmpty())
                <div class="mt-8 rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center dark:border-gray-700 dark:bg-white/[0.03]">
                    <h2 class="text-theme-xl font-semibold text-gray-800 dark:text-white/90">Belum ada reservasi</h2>
                    <p class="mt-2 text-theme-sm text-gray-500 dark:text-gray-400">Reservasi yang Anda buat akan muncul di halaman ini.</p>
                    <a href="{{ route('reservasi.create') }}" class="mt-5 inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Mulai Reservasi</a>
                </div>
            @else
                <div class="mt-8 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                    <div class="max-w-full overflow-x-auto">
                        <table class="w-full min-w-[720px]">
                            <thead>
                                <tr class="border-b border-gray-100 text-start dark:border-gray-800">
                                    <th class="px-5 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Mobil</th>
                                    <th class="px-5 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Tanggal sewa</th>
                                    <th class="px-5 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Durasi</th>
                                    <th class="px-5 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Total biaya</th>
                                    <th class="px-5 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Status</th>
                                    <th class="px-5 py-3 text-end text-theme-xs font-medium text-gray-500 dark:text-gray-400">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reservasis as $reservasi)
                                    @php
                                        $statusColor = match ($reservasi->status) {
                                            'menunggu_konfirmasi' => 'warning',
                                            'disetujui' => 'success',
                                            'ditolak' => 'error',
                                            'dibatalkan' => 'light',
                                            'selesai' => 'primary',
                                        };
                                    @endphp
                                    <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                        <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $reservasi->mobil->nama_mobil }}</td>
                                        <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-300">{{ $reservasi->tanggal_mulai->format('d M Y') }} – {{ $reservasi->tanggal_selesai->format('d M Y') }}</td>
                                        <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-300">{{ $reservasi->durasi_sewa }} hari</td>
                                        <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-300">Rp{{ number_format((float) $reservasi->total_biaya, 0, ',', '.') }}</td>
                                        <td class="px-5 py-4"><x-ui.badge :color="$statusColor">{{ ucwords(str_replace('_', ' ', $reservasi->status)) }}</x-ui.badge></td>
                                        <td class="px-5 py-4 text-end">
                                            <div class="flex flex-col items-end gap-2">
                                                <a href="{{ route('reservasi.show', $reservasi) }}" class="text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">Lihat</a>
                                                @if ($reservasi->status === 'selesai' && !$reservasi->rating)
                                                    <a href="{{ route('reservasi.show', $reservasi) }}#rating" class="text-theme-xs font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">Beri Rating</a>
                                                @elseif ($reservasi->rating)
                                                    <span class="text-theme-xs font-medium text-success-700 dark:text-success-400">Sudah dinilai: {{ $reservasi->rating->rating }}/5</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($reservasis->hasPages())
                    <div class="pt-4">{{ $reservasis->links() }}</div>
                @endif
            @endif
        </section>
    </main>
@endsection
