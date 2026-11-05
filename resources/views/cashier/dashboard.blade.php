@extends('layouts.cashier')
@section('title', 'Dashboard Kasir')
@section('content')
  @include('shared.dashboard', ['prefix' => 'kasir'])
@endsection
