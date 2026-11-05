@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
  @include('shared.dashboard', ['prefix' => 'admin'])
@endsection
