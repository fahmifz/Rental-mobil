@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail Pengembalian Mobil" />

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <x-common.component-card title="Pengembalian Reservasi #{{ $reservasi->id }}" desc="Rincian waktu pengembalian aktual, kondisi fisik kendaraan, denda keterlambatan, dan status akhir.">
        <div class="space-y-6">
            <!-- Summary Information Grid -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Data Sewa & Pelanggan -->
                <div class="rounded-xl border border-gray-200 p-5 dark:border-gray-800">
                    <h3 class="mb-3 text-theme-sm font-semibold text-gray-900 dark:text-white">Informasi Penyewaan</h3>
                    <dl class="divide-y divide-gray-100 dark:divide-gray-800">
                        <div class="flex justify-between py-2 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Pelanggan</dt>
                            <dd class="font-medium text-gray-800 dark:text-white/90">{{ $reservasi->user->name }} ({{ $reservasi->user->email }})</dd>
                        </div>
                        <div class="flex justify-between py-2 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Mobil</dt>
                            <dd class="font-medium text-gray-800 dark:text-white/90">{{ $reservasi->mobil->nama_mobil }} · <span class="font-mono">{{ $reservasi->mobil->plat_nomor }}</span></dd>
                        </div>
                        <div class="flex justify-between py-2 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Jadwal Sewa</dt>
                            <dd class="font-medium text-gray-800 dark:text-white/90">{{ $reservasi->tanggal_mulai->format('d M Y') }} – {{ $reservasi->tanggal_selesai->format('d M Y') }}</dd>
                        </div>
                        <div class="flex justify-between py-2 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Waktu Selesai yang Direncanakan</dt>
                            <dd class="font-semibold text-brand-600 dark:text-brand-400">{{ $reservasi->tanggal_selesai->format('d M Y') }}, 23:59 WITA</dd>
                        </div>
                        <div class="flex justify-between py-2 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Total Biaya Sewa Pokok</dt>
                            <dd class="font-semibold text-gray-900 dark:text-white">Rp{{ number_format((float) $reservasi->total_biaya, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex justify-between py-2 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Total Kewajiban Pembayaran</dt>
                            <dd class="font-semibold text-gray-900 dark:text-white">Rp{{ number_format($reservasi->totalAmountDue(), 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex justify-between py-2 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Sisa Tagihan</dt>
                            <dd class="font-semibold text-brand-700 dark:text-brand-300">Rp{{ number_format($reservasi->remainingAmount(), 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex justify-between py-2 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Status Pembayaran</dt>
                            <dd>
                                @php
                                    $pStatus = $reservasi->paymentStatus();
                                @endphp
                                <x-ui.badge :color="$pStatus === 'lunas' ? 'success' : ($pStatus === 'dibayar_sebagian' ? 'warning' : 'error')">
                                    {{ ucwords(str_replace('_', ' ', $pStatus)) }}
                                </x-ui.badge>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Hasil Pengembalian & Keterlambatan -->
                <div class="rounded-xl border border-gray-200 p-5 dark:border-gray-800">
                    <h3 class="mb-3 text-theme-sm font-semibold text-gray-900 dark:text-white">Rincian Pengembalian & Denda</h3>
                    <dl class="divide-y divide-gray-100 dark:divide-gray-800">
                        <div class="flex justify-between py-2 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Waktu Pengembalian Aktual</dt>
                            <dd class="font-semibold text-gray-900 dark:text-white">{{ $pengembalian->tanggal_pengembalian->format('d M Y, H:i') }} WITA</dd>
                        </div>
                        <div class="flex justify-between py-2 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Status Waktu</dt>
                            <dd>
                                @if ($pengembalian->isLate())
                                    <x-ui.badge color="warning">Terlambat {{ $pengembalian->durasi_keterlambatan }} Hari</x-ui.badge>
                                @else
                                    <x-ui.badge color="success">Tepat Waktu</x-ui.badge>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between py-2 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Tarif Denda per Hari</dt>
                            <dd class="font-medium text-gray-800 dark:text-white/90">Rp{{ number_format((float) $pengembalian->tarif_denda_per_hari, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex justify-between py-2 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Total Denda Keterlambatan</dt>
                            <dd class="font-bold text-title-xs {{ $pengembalian->total_denda > 0 ? 'text-warning-600 dark:text-warning-400' : 'text-success-600 dark:text-success-400' }}">
                                Rp{{ number_format((float) $pengembalian->total_denda, 0, ',', '.') }}
                            </dd>
                        </div>
                        <div class="flex justify-between py-2 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Kondisi Mobil</dt>
                            <dd class="font-medium capitalize text-gray-800 dark:text-white/90">{{ str_replace('_', ' ', $pengembalian->kondisi_mobil) }}</dd>
                        </div>
                        <div class="flex justify-between py-2 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Status Mobil Setelah Kembali</dt>
                            <dd>
                                <x-ui.badge :color="$pengembalian->status_mobil_setelah_kembali === 'tersedia' ? 'success' : 'warning'">
                                    {{ ucfirst($pengembalian->status_mobil_setelah_kembali) }}
                                </x-ui.badge>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Catatan Kondisi & Petugas -->
            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                <h4 class="text-theme-sm font-semibold text-gray-900 dark:text-white">Catatan Kondisi / Kerusakan</h4>
                <p class="mt-2 text-theme-sm text-gray-700 dark:text-gray-300">
                    {{ $pengembalian->catatan_kondisi ?: 'Tidak ada catatan kerusakan atau kondisi khusus.' }}
                </p>
                <div class="mt-4 border-t border-gray-200 pt-3 text-theme-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    Diproses oleh: <span class="font-medium text-gray-800 dark:text-gray-200">{{ $pengembalian->diprosesOleh?->name ?? 'Admin' }}</span>
                    pada {{ $pengembalian->created_at->format('d M Y, H:i') }} WITA.
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                <a href="{{ route('admin.pengembalian.index') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">
                    Kembali ke Daftar Pengembalian
                </a>

                <div class="inline-flex items-center gap-2">
                    <a href="{{ route('admin.pembayaran.show', $reservasi) }}"
                        class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-4 py-2.5 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        Lihat Pembayaran
                    </a>
                </div>
            </div>
        </div>
    </x-common.component-card>
@endsection

