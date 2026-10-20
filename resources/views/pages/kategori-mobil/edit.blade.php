@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Car Category" />

    <x-common.component-card title="Edit Car Category" desc="Update the selected rental vehicle category.">
        @include('pages.kategori-mobil._form', [
            'action' => route('kategori-mobil.update', $kategoriMobil),
            'method' => 'PUT',
            'submitLabel' => 'Update Category',
            'kategoriMobil' => $kategoriMobil,
        ])
    </x-common.component-card>
@endsection
