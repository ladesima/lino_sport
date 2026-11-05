@extends('layouts.cashier')
@section('title', 'Kasir POS Sewa Alat')
@section('content')
  @include('shared.pos-index', ['prefix' => 'kasir'])
@endsection
