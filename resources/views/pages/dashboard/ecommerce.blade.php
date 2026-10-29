@extends('layouts.app')

@section('content')
  <x-common.page-breadcrumb pageTitle="Rental Mobil Dashboard" />

  <div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 space-y-6 xl:col-span-7">
      @include('components.ecommerce.ecommerce-metrics', ['stats' => $stats])
    </div>
    <div class="col-span-12 xl:col-span-5">
        @include('components.ecommerce.monthly-target', ['stats' => $stats])
    </div>

    <div class="col-span-12">
      @include('components.ecommerce.statistics-chart', ['monthlyReservationChart' => $monthlyReservationChart])
    </div>

    <div class="col-span-12">
      @include('components.ecommerce.recent-orders', ['recentReservations' => $recentReservations])
    </div>
  </div>
@endsection
