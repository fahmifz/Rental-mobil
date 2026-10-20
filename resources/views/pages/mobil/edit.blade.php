@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Mobil" />

    <x-common.component-card title="Edit Mobil" desc="Perbarui data mobil pada armada rental.">
        @include('pages.mobil._form', [
            'action' => route('mobil.update', $mobil),
            'method' => 'PUT',
            'submitLabel' => 'Perbarui Mobil',
            'mobil' => $mobil,
        ])
    </x-common.component-card>
@endsection
