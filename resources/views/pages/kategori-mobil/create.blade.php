@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Add Car Category" />

    <x-common.component-card title="New Car Category" desc="Create a category to group rental vehicles.">
        @include('pages.kategori-mobil._form', [
            'action' => route('kategori-mobil.store'),
            'method' => 'POST',
            'submitLabel' => 'Save Category',
        ])
    </x-common.component-card>
@endsection
