@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Tambah Mobil" />

    <x-common.component-card title="Tambah Mobil" desc="Tambahkan mobil baru ke armada rental.">
        @include('pages.mobil._form', [
            'action' => route('mobil.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan Mobil',
        ])
    </x-common.component-card>
@endsection
