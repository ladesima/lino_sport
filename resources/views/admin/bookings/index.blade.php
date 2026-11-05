@extends('layouts.admin')
@section('title', 'Daftar Booking')
@section('content')
  @include('shared.bookings-index', ['prefix' => 'admin'])
@endsection
