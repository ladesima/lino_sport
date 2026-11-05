@extends('layouts.cashier')
@section('title', 'Jadwal Lapangan')
@section('content')
  @include('shared.bookings-schedule', ['prefix' => 'kasir'])
@endsection
