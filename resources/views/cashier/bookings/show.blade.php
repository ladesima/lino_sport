@extends('layouts.cashier')
@section('title', 'Tagihan #' . $booking->formatted_invoice_no)
@section('content')
  @include('shared.bookings-show', ['prefix' => 'kasir'])
@endsection
