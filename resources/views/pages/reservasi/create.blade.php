@extends('layouts.landing')

@section('content')
    <main class="min-h-screen bg-gray-50 px-4 py-10 dark:bg-gray-900 sm:px-6">
        <section
            x-data="{
                today: @js(today()->toDateString()),
                startDate: @js(old('tanggal_mulai')),
                endDate: @js(old('tanggal_selesai')),
                selectedRate: 0,
                updateRate(select) {
                    this.selectedRate = Number(select.selectedOptions[0]?.dataset.rate || 0);
                },
                get duration() {
                    if (!this.startDate || !this.endDate) return 0;
                    const start = new Date(`${this.startDate}T00:00:00`);
                    const end = new Date(`${this.endDate}T00:00:00`);
                    const days = Math.round((end - start) / 86400000) + 1;
                    return days > 0 ? days : 0;
                },
                get total() {
                    return this.duration * this.selectedRate;
                },
                formatRupiah(value) {
                    return `Rp${new Intl.NumberFormat('id-ID').format(value)}`;
                }
            }"
            class="mx-auto w-full max-w-3xl rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-8"
        >
            <a href="{{ route('account') }}" class="text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">Kembali ke akun</a>
            <h1 class="mt-4 text-title-sm font-semibold text-gray-900 dark:text-white">Buat Reservasi</h1>
            <p class="mt-2 text-theme-sm text-gray-600 dark:text-gray-300">Pilih mobil dan tanggal sewa. Reservasi akan menunggu konfirmasi admin.</p>

            @if ($mobils->isEmpty())
                <div class="mt-6 rounded-xl border border-warning-200 bg-warning-50 px-4 py-3 text-theme-sm text-warning-700 dark:border-warning-500/20 dark:bg-warning-500/15 dark:text-warning-400">
                    Belum ada mobil yang tersedia untuk dipesan.
                </div>
            @else
                <form action="{{ route('reservasi.store') }}" method="POST" class="mt-8 space-y-6">
                    @csrf

                    <div>
                        <label for="mobil_id" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">Mobil <span class="text-error-500">*</span></label>
                        <select
                            id="mobil_id"
                            name="mobil_id"
                            x-ref="mobil"
                            @change="updateRate($refs.mobil)"
                            x-init="updateRate($refs.mobil)"
                            required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                        >
                            <option value="" data-rate="0">Pilih mobil</option>
                            @foreach ($mobils as $mobil)
                                <option value="{{ $mobil->id }}" data-rate="{{ $mobil->harga_sewa_per_hari }}" @selected((string) old('mobil_id') === (string) $mobil->id)>
                                    {{ $mobil->nama_mobil }} — {{ $mobil->merek }} (Rp{{ number_format((float) $mobil->harga_sewa_per_hari, 0, ',', '.') }}/hari)
                                </option>
                            @endforeach
                        </select>
                        @error('mobil_id')<p class="mt-1.5 text-theme-sm text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label for="tanggal_mulai" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">Tanggal mulai sewa <span class="text-error-500">*</span></label>
                            <input id="tanggal_mulai" name="tanggal_mulai" type="date" min="{{ today()->toDateString() }}" x-model="startDate" required
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            @error('tanggal_mulai')<p class="mt-1.5 text-theme-sm text-error-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="tanggal_selesai" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">Tanggal selesai sewa <span class="text-error-500">*</span></label>
                            <input id="tanggal_selesai" name="tanggal_selesai" type="date" :min="startDate || today" x-model="endDate" required
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            @error('tanggal_selesai')<p class="mt-1.5 text-theme-sm text-error-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="rounded-xl border border-brand-100 bg-brand-50 p-4 dark:border-brand-500/20 dark:bg-brand-500/10">
                        <dl class="grid gap-3 sm:grid-cols-3">
                            <div>
                                <dt class="text-theme-xs text-gray-600 dark:text-gray-300">Tarif harian</dt>
                                <dd class="mt-1 font-semibold text-gray-900 dark:text-white" x-text="formatRupiah(selectedRate)"></dd>
                            </div>
                            <div>
                                <dt class="text-theme-xs text-gray-600 dark:text-gray-300">Durasi sewa</dt>
                                <dd class="mt-1 font-semibold text-gray-900 dark:text-white"><span x-text="duration"></span> hari</dd>
                            </div>
                            <div>
                                <dt class="text-theme-xs text-gray-600 dark:text-gray-300">Estimasi total biaya</dt>
                                <dd class="mt-1 font-semibold text-brand-700 dark:text-brand-300" x-text="formatRupiah(total)"></dd>
                            </div>
                        </dl>
                        <p class="mt-3 text-theme-xs text-gray-600 dark:text-gray-300">Perhitungan di halaman ini hanya estimasi. Tarif, durasi, dan total akan dihitung ulang oleh server saat reservasi dikirim.</p>
                    </div>

                    <div class="flex flex-wrap justify-end gap-3">
                        <a href="{{ route('reservasi.index') }}" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-white/[0.03]">Reservasi Saya</a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Kirim Reservasi</button>
                    </div>
                </form>
            @endif
        </section>
    </main>
@endsection
