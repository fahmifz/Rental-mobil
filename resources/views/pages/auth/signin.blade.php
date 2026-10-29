@extends('layouts.fullscreen-layout')

@section('content')
    <div class="min-h-screen bg-white p-6 dark:bg-gray-900 sm:p-0">
        <div class="mx-auto flex min-h-screen w-full max-w-7xl items-center justify-center lg:grid lg:grid-cols-2">
            <div class="w-full max-w-md py-10 lg:py-0">
                <a href="{{ route('home') }}" class="mb-10 inline-flex items-center gap-2 text-theme-sm text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="size-5 stroke-current rtl:rotate-180" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m12.7 5-5.2 5.2 5.2 5.2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    Kembali ke beranda
                </a>

                <div class="mb-8">
                    <p class="text-theme-sm font-semibold text-brand-600 dark:text-brand-400">Awal Putra Rentcar</p>
                    <h1 class="mt-2 text-title-sm font-semibold text-gray-900 dark:text-white">Masuk ke akun</h1>
                    <p class="mt-2 text-theme-sm text-gray-500 dark:text-gray-400">Gunakan email dan password akun Anda.</p>
                </div>

                <form action="{{ route('login.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Email <span class="text-error-500">*</span></label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                            class="h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 {{ $errors->has('email') ? 'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-500' : 'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-800' }}">
                        @error('email')<p class="mt-1.5 text-theme-sm text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Password <span class="text-error-500">*</span></label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 {{ $errors->has('password') ? 'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-500' : 'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-800' }}">
                        @error('password')<p class="mt-1.5 text-theme-sm text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <label class="flex cursor-pointer items-center gap-2 text-theme-sm text-gray-600 dark:text-gray-400">
                        <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                        Ingat saya
                    </label>

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Masuk</button>
                </form>

                <p class="mt-6 text-theme-sm text-gray-600 dark:text-gray-400">Belum punya akun? <a href="{{ route('register') }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">Daftar sebagai penyewa</a></p>
            </div>

            <div class="hidden h-full items-center justify-center bg-brand-950 p-12 lg:flex">
                <div class="max-w-sm text-center">
                    <img src="/images/hero/logo.png" alt="Awal Putra Rentcar" width="100" height="100" class="mx-auto">
                    <h2 class="mt-6 text-title-sm font-semibold text-white">Awal Putra Rentcar</h2>
                    <p class="mt-3 text-theme-sm text-white/70">Masuk untuk mengakses akun Anda.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
