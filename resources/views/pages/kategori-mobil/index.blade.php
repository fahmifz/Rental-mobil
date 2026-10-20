@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Car Categories" />

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <x-common.component-card title="Car Categories" desc="Manage the vehicle categories available for rental.">
        <div class="flex justify-end">
            <a href="{{ route('kategori-mobil.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                Add Car Category
            </a>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[640px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-start sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Category Name</p>
                            </th>
                            <th class="px-5 py-3 text-start sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Description</p>
                            </th>
                            <th class="px-5 py-3 text-start sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Created At</p>
                            </th>
                            <th class="px-5 py-3 text-end sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kategoriMobils as $kategoriMobil)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                        {{ $kategoriMobil->nama_kategori }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="max-w-md text-gray-500 text-theme-sm dark:text-gray-400">
                                        {{ $kategoriMobil->deskripsi ?: '—' }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                        {{ $kategoriMobil->created_at?->format('d M Y') }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 text-end sm:px-6">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('kategori-mobil.edit', $kategoriMobil) }}"
                                            class="rounded-lg px-3 py-2 text-sm font-medium text-brand-500 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/15">
                                            Edit
                                        </a>
                                        <form action="{{ route('kategori-mobil.destroy', $kategoriMobil) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg px-3 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/15">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No car categories have been created yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($kategoriMobils->hasPages())
            <div class="pt-2">
                {{ $kategoriMobils->links() }}
            </div>
        @endif
    </x-common.component-card>
@endsection
