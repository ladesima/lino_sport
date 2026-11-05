@extends('layouts.admin')
@section('title', 'Tagihan #' . $booking->formatted_invoice_no)
@section('content')
  @include('shared.bookings-show', ['prefix' => 'admin'])
@endsection
