@extends('layouts.admin')
@section('title', 'Jadwal Lapangan')
@section('content')
  @include('shared.bookings-schedule', ['prefix' => 'admin'])
@endsection
