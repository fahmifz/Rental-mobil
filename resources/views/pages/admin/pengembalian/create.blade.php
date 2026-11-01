@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Proses Pengembalian Mobil" />

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/15 dark:text-error-400" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <x-common.component-card title="Pengembalian Reservasi #{{ $reservasi->id }}" desc="Form pencatatan waktu pengembalian, kondisi unit, status akhir kendaraan, dan kalkulasi denda.">
        @php
            $deadlineStr = $reservasi->tanggal_selesai->format('Y-m-d') . 'T23:59:59';
            $tarifDenda = (float) config('rental.tarif_denda_per_hari', 50000.0);
        @endphp

        <div class="grid gap-6 lg:grid-cols-12"
            x-data="{
                returnTime: '{{ old('tanggal_pengembalian', $defaultReturnTime) }}',
                deadline: new Date('{{ $deadlineStr }}'),
                dailyRate: {{ $tarifDenda }},
                lateDays: {{ $initialCalc['durasi_keterlambatan'] }},
                totalFine: {{ $initialCalc['total_denda'] }},
                isLate: {{ $initialCalc['is_late'] ? 'true' : 'false' }},

                recalculate() {
                    if (!this.returnTime) return;
                    const returnDate = new Date(this.returnTime);
                    const diffMs = returnDate.getTime() - this.deadline.getTime();

                    if (diffMs > 0) {
                        const diffSeconds = Math.floor(diffMs / 1000);
                        this.lateDays = Math.ceil(diffSeconds / 86400);
                        this.totalFine = this.lateDays * this.dailyRate;
                        this.isLate = true;
                    } else {
                        this.lateDays = 0;
                        this.totalFine = 0;
                        this.isLate = false;
                    }
                },
                formatRupiah(amount) {
                    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(amount);
                }
            }">

            <!-- Left: Reservation Information & Summary -->
            <div class="space-y-6 lg:col-span-5">
                <dl class="rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                    <h3 class="mb-4 text-theme-sm font-semibold text-gray-900 dark:text-white">Ringkasan Reservasi</h3>

                    <div class="flex justify-between gap-3 border-b border-gray-100 py-2.5 dark:border-gray-800">
                        <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Pelanggan</dt>
                        <dd class="text-end text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $reservasi->user->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 border-b border-gray-100 py-2.5 dark:border-gray-800">
                        <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Mobil</dt>
                        <dd class="text-end text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $reservasi->mobil->nama_mobil }} ({{ $reservasi->mobil->plat_nomor }})</dd>
                    </div>
                    <div class="flex justify-between gap-3 border-b border-gray-100 py-2.5 dark:border-gray-800">
                        <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Jadwal Sewa</dt>
                        <dd class="text-end text-theme-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $reservasi->tanggal_mulai->format('d M Y') }} – {{ $reservasi->tanggal_selesai->format('d M Y') }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3 border-b border-gray-100 py-2.5 dark:border-gray-800">
                        <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Batas Pengembalian</dt>
                        <dd class="text-end text-theme-sm font-semibold text-brand-600 dark:text-brand-400">
                            {{ $reservasi->tanggal_selesai->format('d M Y') }}, 23:59 WITA
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3 border-b border-gray-100 py-2.5 dark:border-gray-800">
                        <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Total Biaya Sewa</dt>
                        <dd class="text-end text-theme-sm font-semibold text-gray-900 dark:text-white">
                            Rp{{ number_format((float) $reservasi->total_biaya, 0, ',', '.') }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3 py-2.5">
                        <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Status Pembayaran</dt>
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

                <!-- Live Penalty Estimation Box -->
                <div class="rounded-xl border p-5 transition-colors"
                    :class="isLate
                        ? 'border-warning-200 bg-warning-50/60 dark:border-warning-500/20 dark:bg-warning-500/10'
                        : 'border-success-200 bg-success-50/60 dark:border-success-500/20 dark:bg-success-500/10'">
                    <div class="flex items-center justify-between">
                        <h4 class="text-theme-sm font-semibold"
                            :class="isLate ? 'text-warning-800 dark:text-warning-300' : 'text-success-800 dark:text-success-300'">
                            Estimasi Denda Keterlambatan
                        </h4>
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-theme-xs font-semibold"
                            :class="isLate
                                ? 'bg-warning-100 text-warning-800 dark:bg-warning-500/20 dark:text-warning-300'
                                : 'bg-success-100 text-success-800 dark:bg-success-500/20 dark:text-success-300'"
                            x-text="isLate ? 'Terlambat' : 'Tepat Waktu'">
                        </span>
                    </div>

                    <div class="mt-4 space-y-2 text-theme-sm">
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span>Durasi keterlambatan:</span>
                            <span class="font-medium text-gray-900 dark:text-white" x-text="lateDays + ' hari'"></span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span>Tarif denda per hari:</span>
                            <span class="font-medium text-gray-900 dark:text-white">Rp{{ number_format($tarifDenda, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-dashed border-gray-300 pt-2 text-theme-base font-bold dark:border-gray-700 flex justify-between"
                            :class="isLate ? 'text-warning-700 dark:text-warning-400' : 'text-success-700 dark:text-success-400'">
                            <span>Total Denda:</span>
                            <span>Rp<span x-text="formatRupiah(totalFine)"></span></span>
                        </div>
                    </div>
                    <p class="mt-3 text-theme-xs text-gray-500 dark:text-gray-400">
                        * Durasi keterlambatan dihitung otomatis dari batas akhir sewa dan dibulatkan ke atas per hari keterlambatan.
                    </p>
                </div>
            </div>

            <!-- Right: Return Input Form -->
            <div class="lg:col-span-7">
                <form action="{{ route('admin.pengembalian.store', $reservasi) }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="tanggal_pengembalian" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                            Waktu Pengembalian Aktual <span class="text-error-500">*</span>
                        </label>
                        <input id="tanggal_pengembalian" name="tanggal_pengembalian" type="datetime-local"
                            x-model="returnTime" @change="recalculate()" @input="recalculate()" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('tanggal_pengembalian')
                            <p class="mt-1.5 text-theme-sm text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="kondisi_mobil" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                Kondisi Mobil <span class="text-error-500">*</span>
                            </label>
                            <select id="kondisi_mobil" name="kondisi_mobil" required
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Pilih kondisi</option>
                                <option value="baik" @selected(old('kondisi_mobil') === 'baik')>Baik (Normal)</option>
                                <option value="rusak_ringan" @selected(old('kondisi_mobil') === 'rusak_ringan')>Rusak Ringan (Baret / Lecet)</option>
                                <option value="rusak_berat" @selected(old('kondisi_mobil') === 'rusak_berat')>Rusak Berat (Kendala Mesin / Tabrakan)</option>
                            </select>
                            @error('kondisi_mobil')
                                <p class="mt-1.5 text-theme-sm text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status_mobil_setelah_kembali" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                                Status Akhir Mobil <span class="text-error-500">*</span>
                            </label>
                            <select id="status_mobil_setelah_kembali" name="status_mobil_setelah_kembali" required
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="tersedia" @selected(old('status_mobil_setelah_kembali', 'tersedia') === 'tersedia')>Tersedia (Siap Disewa)</option>
                                <option value="perawatan" @selected(old('status_mobil_setelah_kembali') === 'perawatan')>Perawatan (Perlu Bengkel / Servis)</option>
                            </select>
                            @error('status_mobil_setelah_kembali')
                                <p class="mt-1.5 text-theme-sm text-error-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="catatan_kondisi" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                            Catatan Kondisi / Kerusakan Khusus (Opsional)
                        </label>
                        <textarea id="catatan_kondisi" name="catatan_kondisi" rows="4"
                            placeholder="Tuliskan catatan kondisi mobil saat dikembalikan, kelengkapan surat, sisa BBM, atau kondisi khusus lainnya..."
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('catatan_kondisi') }}</textarea>
                        @error('catatan_kondisi')
                            <p class="mt-1.5 text-theme-sm text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-3 pt-4">
                        <a href="{{ route('admin.pengembalian.index') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">
                            Batal
                        </a>
                        <button type="submit" onclick="return confirm('Apakah Anda yakin data pengembalian dan kalkulasi denda sudah sesuai?')"
                            class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                            Simpan Pengembalian Mobil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </x-common.component-card>
@endsection

