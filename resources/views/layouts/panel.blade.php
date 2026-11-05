{{--
  Shell bersama untuk Admin Panel & Portal Kasir.
  Layout turunan (layouts.admin / layouts.cashier) cukup mengisi section:
  - nav        : daftar menu sidebar
  - nav_footer : tautan tambahan di bawah sidebar
  - panel_tag  : label kecil di bawah nama brand
--}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') · Lino Sport Center</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <link href="{{ asset('css/lino-ui.css') }}?v={{ @filemtime(public_path('css/lino-ui.css')) }}" rel="stylesheet">
  @yield('styles')
</head>
<body>
<div class="lx-app">

  {{-- Sidebar: tetap di desktop, offcanvas di layar < 992px --}}
  <aside class="lx-sidebar offcanvas-lg offcanvas-start d-print-none" tabindex="-1" id="lxSidebar" aria-label="Navigasi utama">
    <div class="d-flex align-items-center justify-content-between">
      <a href="@yield('home_url', url('/'))" class="lx-brand flex-grow-1">
        <img src="{{ asset('images/logo.png') }}" alt="Logo Lino Sport">
        <span>
          <span class="lx-brand-name d-block">Lino Sport</span>
          <span class="lx-brand-tag">@yield('panel_tag', 'Panel')</span>
        </span>
      </a>
      <button type="button" class="btn btn-ghost btn-icon me-2 d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#lxSidebar" aria-label="Tutup menu">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <div class="offcanvas-body">
      <nav class="lx-nav">
        @yield('nav')
      </nav>

      <div class="lx-nav-footer">
        <div class="lx-nav mb-2 p-0">
          @yield('nav_footer')
        </div>
        <div class="lx-user">
          <span class="lx-avatar">{{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}</span>
          <div class="min-w-0 flex-grow-1" style="min-width:0">
            <div class="lx-user-name text-truncate">{{ auth()->user()?->name ?? 'Pengguna' }}</div>
            <div class="lx-user-role text-truncate">{{ auth()->user()?->role_name ?? '' }}</div>
          </div>
          <form action="{{ route('logout') }}" method="POST" data-no-loading>
            @csrf
            <button type="submit" class="btn btn-ghost btn-icon text-danger" title="Keluar" aria-label="Keluar">
              <i class="bi bi-box-arrow-right"></i>
            </button>
          </form>
        </div>
      </div>
    </div>
  </aside>

  <div class="lx-main">
    <header class="lx-topbar d-print-none">
      <button class="lx-icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#lxSidebar" aria-controls="lxSidebar" aria-label="Buka menu">
        <i class="bi bi-list fs-5"></i>
      </button>
      <h1 class="lx-topbar-title">@yield('title', 'Dashboard')</h1>
      <div class="ms-auto d-flex align-items-center gap-2">
        <span class="text-muted small d-none d-md-inline">
          <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::now()->translatedFormat('l, d M Y') }}
          <span class="mx-1">·</span><span id="lxClock">{{ \Carbon\Carbon::now()->format('H:i') }}</span>
        </span>
        @yield('topbar_actions')
      </div>
    </header>

    <main class="lx-content">
      @include('layouts.partials.flash')
      @yield('content')

      <footer class="mt-4 pt-3 border-top text-center text-muted small d-print-none">
        &copy; {{ date('Y') }} <strong>Lino Sport Center</strong> &middot; Developed by <strong class="text-dark">Muhammad Musyary, S.Kom</strong>
      </footer>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script src="{{ asset('js/lino-ui.js') }}?v={{ @filemtime(public_path('js/lino-ui.js')) }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.AOS) AOS.init({ duration: 400, once: true, offset: 20 });
    var clock = document.getElementById('lxClock');
    if (clock) setInterval(function () {
      var d = new Date();
      clock.textContent = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
    }, 15000);
  });
</script>
@include('layouts.partials.admin-alerts')
@yield('scripts')
</body>
</html>
