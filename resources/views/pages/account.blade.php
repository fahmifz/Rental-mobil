@extends('layouts.landing')

@section('content')
    <main class="flex min-h-screen items-center bg-gray-50 px-4 py-10 dark:bg-gray-900 sm:px-6">
        <section class="mx-auto w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-theme-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400">{{ session('success') }}</div>
            @endif

            <p class="text-theme-sm font-semibold text-brand-600 dark:text-brand-400">Awal Putra Rentcar</p>
            <h1 class="mt-2 text-title-sm font-semibold text-gray-900 dark:text-white">Akun Penyewa</h1>
            <p class="mt-3 text-theme-sm text-gray-600 dark:text-gray-300">Anda masuk sebagai pelanggan.</p>

            <dl class="mt-8 space-y-4 border-y border-gray-100 py-5 dark:border-gray-800">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Nama</dt>
                    <dd class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $user->name }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Email</dt>
                    <dd class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $user->email }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('reservasi.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Buat Reservasi</a>
                <a href="{{ route('reservasi.index') }}" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-white/[0.03]">Reservasi Saya</a>
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-white/[0.03]">Kembali ke Beranda</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Keluar</button>
                </form>
            </div>
        </section>
    </main>
@endsection
