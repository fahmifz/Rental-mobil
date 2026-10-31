@extends('layouts.landing')

@section('content')
    <main class="min-h-screen bg-gray-50 px-4 py-10 dark:bg-gray-900 sm:px-6 sm:py-14">
        <section class="mx-auto w-full max-w-5xl">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-theme-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400">{{ session('success') }}</div>
            @endif

            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <a href="{{ route('home') }}" class="text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">Kembali ke Beranda</a>
                    <p class="mt-4 text-theme-sm font-semibold text-brand-600 dark:text-brand-400">Awal Putra Rentcar</p>
                    <h1 class="mt-2 text-title-sm font-semibold text-gray-900 dark:text-white">Akun Penyewa</h1>
                </div>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]" aria-labelledby="profil-heading">
                    <div class="border-b border-gray-100 px-5 py-5 dark:border-gray-800 sm:px-6">
                        <h2 id="profil-heading" class="text-theme-xl font-semibold text-gray-900 dark:text-white">Profil Saya</h2>
                        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Informasi akun pelanggan Anda.</p>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <div class="flex size-16 shrink-0 items-center justify-center rounded-full bg-brand-50 text-title-sm font-semibold text-brand-700 ring-1 ring-brand-100 dark:bg-brand-500/15 dark:text-brand-300 dark:ring-brand-500/20" aria-hidden="true">
                                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-theme-xl font-semibold text-gray-900 dark:text-white">{{ $user->name }}</p>
                                <p class="mt-1 break-all text-theme-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                                <span class="mt-3 inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-theme-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">Akun Pelanggan</span>
                            </div>
                        </div>

                        <dl class="mt-6 divide-y divide-gray-100 border-y border-gray-100 dark:divide-gray-800 dark:border-gray-800">
                            <div class="grid gap-1 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-center">
                                <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Nama lengkap</dt>
                                <dd class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $user->name }}</dd>
                            </div>
                            <div class="grid gap-1 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-center">
                                <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Alamat email</dt>
                                <dd class="break-all text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $user->email }}</dd>
                            </div>
                            <div class="grid gap-1 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-center">
                                <dt class="text-theme-sm text-gray-500 dark:text-gray-400">Jenis akun</dt>
                                <dd class="text-theme-sm font-medium text-gray-800 dark:text-white/90">Pelanggan</dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <aside class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-6" aria-label="Navigasi akun">
                    <h2 class="text-theme-xl font-semibold text-gray-900 dark:text-white">Menu Akun</h2>
                    <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Kelola aktivitas rental Anda.</p>

                    <div class="mt-6 flex flex-col gap-3">
                        <a href="{{ route('reservasi.create') }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-3 focus:ring-brand-500/20">Buat Reservasi</a>
                        <a href="{{ route('reservasi.index') }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-white/[0.03]">Reservasi Saya</a>
                        <a href="{{ route('home') }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-white/[0.03]">Kembali ke Beranda</a>
                    </div>

                    <div class="mt-5 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-error-200 bg-white px-4 py-2.5 text-theme-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-500/20 dark:bg-white/[0.03] dark:text-error-400 dark:hover:bg-error-500/10">
                                Keluar
                            </button>
                        </form>
                    </div>
                </aside>
            </div>
        </section>
    </main>
@endsection
