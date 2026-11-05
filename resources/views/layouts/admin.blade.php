@extends('layouts.panel')

@section('home_url', route('admin.dashboard'))
@section('panel_tag', auth()->user()?->isSuperAdmin() ? 'Admin Utama' : 'User Admin')

@section('topbar_actions')
  {{-- Lonceng Notifikasi Booking Menunggu Approval --}}
  <div class="dropdown d-inline-block position-relative" id="adminBellDropdownWrap">
    <button class="btn btn-light btn-sm position-relative d-inline-flex align-items-center gap-1.5 px-2.5 rounded-3 border"
            type="button" id="adminBellBtn" data-bs-toggle="dropdown" aria-expanded="false" title="Notifikasi Booking Menunggu Approval">
      <i class="bi bi-bell fs-6" id="adminBellIcon"></i>
      <span class="d-none d-sm-inline small fw-semibold">Notifikasi</span>
      <span class="badge rounded-pill bg-danger d-none" id="adminBellBadge" style="font-size:0.68rem;">0</span>
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0 mt-2" style="width: 320px; max-width: 92vw;" aria-labelledby="adminBellBtn">
      <div class="p-3 bg-dark text-white rounded-top-4 d-flex justify-content-between align-items-center">
        <div class="fw-bold small d-flex align-items-center gap-1.5">
          <i class="bi bi-bell-fill text-warning"></i>
          <span>Approval Booking</span>
        </div>
        <button type="button" class="btn btn-xs btn-outline-light py-0 px-2" style="font-size:0.7rem;" onclick="window.playLinoBell && window.playLinoBell(true)" title="Coba Bunyikan Lonceng">
          <i class="bi bi-volume-up me-1"></i>Tes Suara
        </button>
      </div>
      <div id="adminBellList" class="p-2" style="max-height: 290px; overflow-y: auto;">
        <div class="text-center py-4 text-muted small">
          <i class="bi bi-check2-circle fs-3 text-success d-block mb-1"></i>
          Semua booking telah di-approve
        </div>
      </div>
      <div class="p-2 border-top bg-light rounded-bottom-4 text-center">
        <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="small fw-bold text-success text-decoration-none">
          Buka Antrean Approval &rarr;
        </a>
      </div>
    </div>
  </div>

  <a href="{{ route('tv.display') }}" target="_blank" class="btn btn-light btn-sm d-inline-flex align-items-center gap-1 border" title="Buka Tampilan TV Live">
    <i class="bi bi-tv text-danger"></i> <span class="d-none d-md-inline">TV Live</span>
  </a>
@endsection

@section('nav')
  <div class="lx-nav-label">Menu Operasional</div>
  <a class="lx-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
    <i class="bi bi-grid-1x2"></i> <span>Dashboard</span>
  </a>
  <a class="lx-nav-link {{ request()->routeIs('admin.bookings.schedule') ? 'active' : '' }}" href="{{ route('admin.bookings.schedule') }}">
    <i class="bi bi-calendar3-week"></i> <span>Jadwal Lapangan</span>
  </a>
  @php
    $sidebarPendingCount = \App\Models\Booking::where('status', 'pending')->count();
  @endphp
  <a class="lx-nav-link {{ request()->routeIs('admin.bookings.index') || request()->routeIs('admin.bookings.show') ? 'active' : '' }}" href="{{ route('admin.bookings.index') }}">
    <i class="bi bi-inbox"></i> <span>Daftar Booking & Approval</span>
    <span class="badge rounded-pill bg-danger ms-auto {{ $sidebarPendingCount > 0 ? '' : 'd-none' }}" id="sidebarPendingBadge" style="font-size:0.68rem; padding: 0.28em 0.6em;">
      {{ $sidebarPendingCount > 99 ? '99+' : $sidebarPendingCount }}
    </span>
  </a>

  @if(auth()->user()?->isSuperAdmin())
    <div class="lx-nav-label">Keuangan</div>
    <a class="lx-nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
      <i class="bi bi-bar-chart-line"></i> <span>Laporan Keuangan</span>
    </a>

    <div class="lx-nav-label">Master Data & Konten</div>
    <a class="lx-nav-link {{ request()->routeIs('admin.courts*') ? 'active' : '' }}" href="{{ route('admin.courts.index') }}">
      <i class="bi bi-building"></i> <span>Lapangan</span>
    </a>
    <a class="lx-nav-link {{ request()->routeIs('admin.rates*') ? 'active' : '' }}" href="{{ route('admin.rates.index') }}">
      <i class="bi bi-tag"></i> <span>Tarif Sewa</span>
    </a>
    <a class="lx-nav-link {{ request()->routeIs('admin.sliders*') ? 'active' : '' }}" href="{{ route('admin.sliders.index') }}">
      <i class="bi bi-images"></i> <span>Banner Beranda</span>
    </a>

    <div class="lx-nav-label">Tim & Akses Admin</div>
    <a class="lx-nav-link {{ request()->routeIs('admin.cashiers*') ? 'active' : '' }}" href="{{ route('admin.cashiers.index') }}">
      <i class="bi bi-person-check"></i> <span>Kelola User Admin</span>
    </a>
  @endif
@endsection

@section('nav_footer')
  <a class="lx-nav-link" href="{{ route('home') }}" target="_blank">
    <i class="bi bi-box-arrow-up-right"></i> <span>Lihat Website Publik</span>
  </a>
@endsection
