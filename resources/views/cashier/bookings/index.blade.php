@extends('layouts.cashier')
@section('title', 'Daftar Booking')
@section('content')
  @include('shared.bookings-index', ['prefix' => 'kasir'])
@endsection
