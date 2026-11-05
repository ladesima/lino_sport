@extends('layouts.panel')

@section('home_url', route('kasir.dashboard'))
@section('panel_tag', 'Meja Kasir & Resepsionis')

@section('topbar_actions')
  <a href="{{ route('kasir.pos.index') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
    <i class="bi bi-bag-plus"></i> <span>POS Sewa Alat</span>
  </a>
  <a href="{{ route('tv.display') }}" target="_blank" class="btn btn-light btn-sm d-inline-flex align-items-center gap-1" title="Buka Tampilan TV Live">
    <i class="bi bi-tv text-danger"></i> <span class="d-none d-md-inline">TV Live</span>
  </a>
@endsection

@section('nav')
  <div class="lx-nav-label">Operasional Kasir</div>
  <a class="lx-nav-link {{ request()->routeIs('kasir.dashboard') ? 'active' : '' }}" href="{{ route('kasir.dashboard') }}">
    <i class="bi bi-grid-1x2"></i> <span>Dashboard Hari Ini</span>
  </a>
  <a class="lx-nav-link {{ request()->routeIs('kasir.pos*') ? 'active' : '' }}" href="{{ route('kasir.pos.index') }}">
    <i class="bi bi-bag-plus"></i> <span>Kasir Sewa Alat</span>
    <span class="lx-nav-meta">POS</span>
  </a>
  <a class="lx-nav-link {{ request()->routeIs('kasir.bookings.schedule') ? 'active' : '' }}" href="{{ route('kasir.bookings.schedule') }}">
    <i class="bi bi-calendar3-week"></i> <span>Jadwal Lapangan</span>
  </a>
  <a class="lx-nav-link {{ request()->routeIs('kasir.bookings.index') || request()->routeIs('kasir.bookings.show') ? 'active' : '' }}" href="{{ route('kasir.bookings.index') }}">
    <i class="bi bi-inbox"></i> <span>Daftar Booking</span>
  </a>
@endsection

@section('nav_footer')
  @if(auth()->user() && auth()->user()->isAdmin())
    <a class="lx-nav-link" href="{{ route('admin.dashboard') }}">
      <i class="bi bi-sliders"></i> <span>Ke Admin Panel</span>
      <i class="bi bi-arrow-right ms-auto" style="font-size:.8rem;width:auto"></i>
    </a>
  @endif
  <a class="lx-nav-link" href="{{ route('tv.display') }}" target="_blank">
    <i class="bi bi-tv"></i> <span>Layar TV Arena</span>
  </a>
@endsection
