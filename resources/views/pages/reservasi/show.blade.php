@extends('layouts.landing')

@section('content')
    <main class="min-h-screen bg-gray-50 px-4 py-10 dark:bg-gray-900 sm:px-6">
        <section class="mx-auto w-full max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-theme-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400" role="status">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-theme-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/15 dark:text-error-400" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <a href="{{ route('reservasi.index') }}" class="text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">Kembali ke reservasi saya</a>
            <h1 class="mt-4 text-title-sm font-semibold text-gray-900 dark:text-white">Detail Reservasi</h1>
            <p class="mt-2 text-theme-sm text-gray-600 dark:text-gray-300">Nomor reservasi #{{ $reservasi->id }}</p>

            <dl class="mt-8 divide-y divide-gray-100 border-y border-gray-100 dark:divide-gray-800 dark:border-gray-800">
                <div class="flex flex-wrap justify-between gap-2 py-4">
                    <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Mobil</dt>
                    <dd class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $reservasi->mobil->nama_mobil }} — {{ $reservasi->mobil->merek }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2 py-4">
                    <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Tanggal sewa</dt>
                    <dd class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $reservasi->tanggal_mulai->format('d M Y') }} – {{ $reservasi->tanggal_selesai->format('d M Y') }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2 py-4">
                    <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Tarif harian</dt>
                    <dd class="text-theme-sm font-medium text-gray-800 dark:text-white/90">Rp{{ number_format((float) $reservasi->tarif_harian, 0, ',', '.') }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2 py-4">
                    <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Durasi</dt>
                    <dd class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $reservasi->durasi_sewa }} hari</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2 py-4">
                    <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Total biaya</dt>
                    <dd class="text-theme-sm font-semibold text-gray-900 dark:text-white">Rp{{ number_format((float) $reservasi->total_biaya, 0, ',', '.') }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2 py-4">
                    <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Status</dt>
                    <dd><x-ui.badge :color="match ($reservasi->status) {
                        'menunggu_konfirmasi' => 'warning',
                        'disetujui' => 'success',
                        'ditolak' => 'error',
                        'dibatalkan' => 'light',
                        'selesai' => 'primary',
                    }">{{ ucwords(str_replace('_', ' ', $reservasi->status)) }}</x-ui.badge></dd>
                </div>
            </dl>

            @if ($reservasi->rating)
                <div class="mt-6 rounded-xl border border-success-200 bg-success-50 px-4 py-4 dark:border-success-500/20 dark:bg-success-500/15">
                    <p class="text-theme-sm font-medium text-success-700 dark:text-success-400">Reservasi sudah dinilai</p>
                    <p class="mt-2 text-title-sm font-semibold text-success-700 dark:text-success-400" aria-label="Rating {{ $reservasi->rating->rating }} dari 5">
                        @for ($star = 1; $star <= 5; $star++)
                            <span>{{ $star <= $reservasi->rating->rating ? '★' : '☆' }}</span>
                        @endfor
                        <span class="ms-2 text-theme-sm">{{ $reservasi->rating->rating }}/5</span>
                    </p>
                </div>
            @elseif ($reservasi->status === 'selesai')
                <form id="rating" action="{{ route('reservasi.rating.store', $reservasi) }}" method="POST" class="mt-6 rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                    @csrf
                    <fieldset>
                        <legend class="text-theme-sm font-medium text-gray-800 dark:text-white/90">Beri rating untuk mobil ini</legend>
                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Pilih nilai 1 sampai 5.</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @for ($rating = 1; $rating <= 5; $rating++)
                                <label class="cursor-pointer">
                                    <input class="peer sr-only" type="radio" name="rating" value="{{ $rating }}" required @checked(old('rating') == $rating)>
                                    <span class="inline-flex min-w-16 items-center justify-center rounded-lg border border-gray-200 px-3 py-2 text-theme-sm text-gray-600 peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700 dark:border-gray-700 dark:text-gray-300 dark:peer-checked:border-brand-500 dark:peer-checked:bg-brand-500/15 dark:peer-checked:text-brand-300">
                                        ★ {{ $rating }}
                                    </span>
                                </label>
                            @endfor
                        </div>
                    </fieldset>
                    <button type="submit" class="mt-5 inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                        Simpan Rating
                    </button>
                </form>
            @endif
        </section>
    </main>
@endsection
