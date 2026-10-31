@extends('layouts.app')

@section('content')
    <div x-data="{ isProfileInfoModal: false }">
        <x-common.page-breadcrumb pageTitle="Profil Akun" />

        @if (session('success'))
            <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/15 dark:text-error-400" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
            <h3 class="mb-5 text-lg font-semibold text-gray-800 lg:mb-7 dark:text-white/90">Profil Saya</h3>

            <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-800 lg:p-6">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-col items-start gap-5 sm:flex-row sm:items-center">
                        <div class="flex size-20 shrink-0 items-center justify-center rounded-full bg-brand-50 text-title-lg font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-300" aria-hidden="true">
                            {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="mb-1 text-lg font-semibold text-gray-800 dark:text-white/90">{{ $user->name }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                            <span class="mt-3 inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-theme-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                                {{ $user->isAdmin() ? 'Administrator' : 'Pelanggan' }}
                            </span>
                        </div>
                    </div>

                    <button type="button" @click="isProfileInfoModal = true"
                        class="flex h-10 w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 hover:text-gray-800 sm:w-auto dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                        Edit Profil
                    </button>
                </div>

                <dl class="mt-6 grid grid-cols-1 gap-4 border-t border-gray-100 pt-6 sm:grid-cols-2 dark:border-gray-800">
                    <div>
                        <dt class="text-xs leading-normal text-gray-500 dark:text-gray-400">Nama</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs leading-normal text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->email }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div x-show="isProfileInfoModal" x-cloak
            class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-5">
            <div class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]" @click="isProfileInfoModal = false"></div>

            <div @click.outside="isProfileInfoModal = false"
                class="no-scrollbar relative flex w-full max-w-[700px] flex-col overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-11">
                <button type="button" @click="isProfileInfoModal = false"
                    class="absolute end-5 top-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition hover:bg-gray-200 hover:text-gray-600 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300"
                    aria-label="Tutup">
                    <svg class="h-6 w-6 fill-current" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z"/>
                    </svg>
                </button>

                <div class="px-2 pe-14">
                    <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Edit Informasi Profil</h4>
                    <p class="mb-6 text-sm text-gray-500 lg:mb-7 dark:text-gray-400">Perbarui nama dan email akun yang sedang login.</p>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="flex flex-col">
                    @csrf
                    @method('PUT')

                    <div class="custom-scrollbar max-h-[450px] overflow-y-auto px-2">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                            <div class="lg:col-span-1">
                                <label for="profile-name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama</label>
                                <input id="profile-name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                @error('name')
                                    <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="lg:col-span-1">
                                <label for="profile-email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email</label>
                                <input id="profile-email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                @error('email')
                                    <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3 px-2 lg:justify-end">
                        <button type="button" @click="isProfileInfoModal = false"
                            class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600 sm:w-auto">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
